<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RoleMiddleware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        $path = $request->getPathInfo();
        // Permitir preflight CORS sin autenticación
        if ($request->getMethod() === 'OPTIONS') {
            return;
        }

        // Permitir tráfico del hub Mercure (la autorización la maneja el propio hub)
        if (preg_match('@^/\.well-known/mercure@', $path)) {
            return;
        }

        // Permitir lectura pública de cursos (listado) sin token
        if ($request->getMethod() === 'GET' && preg_match('@^/api/cursos$@', $path)) {
            return;
        }
        
        // Permitir login y registro sin autenticación (por path)
        if (in_array($path, ['/api/login', '/api/auth/login', '/api/register'])) {
            return;
        }
        
        // Permitir login y registro sin autenticación (por nombre de ruta)
        if (in_array($route, ['api_login', 'api_register'])) {
            return;
        }
        
        // Obtener el payload JWT que guardó el JwtAuthenticator
        $jwtPayload = $request->attributes->get('jwt_payload');
        
        if (!$jwtPayload) {
            throw new AccessDeniedHttpException('No autenticado.');
        }

        // Token de Mercure para EventSource
        if ($path === '/api/mercure-token') {
            return;
        }
        
        $userRoles = $jwtPayload->roles ?? [];

        // Reglas por prefijo de path (más robusto que nombres de ruta auto-generados)
        $pathRules = [
            'admin' => ['^/api'],
            'docente' => ['^/api/calificaciones', '^/api/reportes', '^/api/matriculas', '^/api/users', '^/api/curso-asignaturas', '^/api/cursos', '^/api/asignaturas', '^/api/chats', '^/api/chat-users', '^/api/mensajes', '^/api/mercure-token'],
            'estudiante' => ['^/api/calificaciones', '^/api/asignaturas', '^/api/reportes', '^/api/matriculas', '^/api/matricula-asignaturas', '^/api/chats', '^/api/chat-users', '^/api/mensajes', '^/api/curso-asignaturas', '^/api/mercure-token'],
            'representante' => ['^/api/calificaciones', '^/api/matriculas', '^/api/matricula-asignaturas', '^/api/chats', '^/api/chat-users', '^/api/mensajes', '^/api/curso-asignaturas', '^/api/reportes', '^/api/mercure-token'],
            // alias para tokens que vienen como PADRE
            'padre' => ['^/api/calificaciones', '^/api/matriculas', '^/api/matricula-asignaturas', '^/api/chats', '^/api/chat-users', '^/api/mensajes', '^/api/curso-asignaturas', '^/api/reportes', '^/api/mercure-token'],
        ];

        $allowed = false;
        foreach ($userRoles as $role) {
            $role = strtolower($role);
            // Admin: acceso total
            if ($role === 'admin') {
                $allowed = true;
                break;
            }

            if (!isset($pathRules[$role])) {
                continue;
            }

            foreach ($pathRules[$role] as $pattern) {
                if (preg_match('@' . $pattern . '@', $path)) {
                    $allowed = true;
                    break 2;
                }
            }
        }

        if (!$allowed) {
            throw new AccessDeniedHttpException('No tienes permiso para acceder a esta ruta.');
        }
    }
}
