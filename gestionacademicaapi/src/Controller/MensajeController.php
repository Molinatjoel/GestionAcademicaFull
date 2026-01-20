<?php
namespace App\Controller;

use App\Service\MensajeService;
use App\Request\MensajeRequest;
use App\Entity\Mensaje;
use App\Entity\Chat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MensajeController extends AbstractController
{
    private MensajeService $mensajeService;
    private ValidatorInterface $validator;
    private Security $security;

    // Inyección del servicio MensajeService y el validador
    public function __construct(MensajeService $mensajeService, ValidatorInterface $validator, Security $security)
    {
        $this->mensajeService = $mensajeService;
        $this->validator = $validator;
        $this->security = $security;
    }

    // Crear mensaje
    #[Route('/api/mensajes', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true);
        // Forzar emisor = usuario autenticado
        $data['id_emisor'] = $user->getId();

        if (!$this->mensajeService->canSendMensaje($user, $data)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }

        $mensajeRequest = new MensajeRequest();
        $mensajeRequest->id_chat = $data['id_chat'] ?? null;
        $mensajeRequest->id_emisor = $data['id_emisor'] ?? null;
        $mensajeRequest->contenido = $data['contenido'] ?? null;
        $errors = $this->validator->validate($mensajeRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            //Errores de validación
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $mensaje = $this->mensajeService->createMensaje($data);
        return $this->json($mensaje);
    }

    #[Route('/api/chats/{id}/mensajes', methods: ['GET'])]
    public function listByChat(int $id): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        try {
            $mensajes = $this->mensajeService->getMensajesDeChat($user, $id);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $payload = array_map(function (Mensaje $m) {
            $emisor = $m->getEmisor();
            $chat = $m->getChat();
            return [
                'id_mensaje' => $m->getIdMensaje(),
                'contenido' => $m->getContenido(),
                'fecha_envio' => $m->getFechaEnvio()?->format('c'),
                'id_chat' => $chat?->getIdChat(),
                'emisor' => $emisor ? [
                    'id' => $emisor->getId(),
                    'nombre' => trim(($emisor->getNombres() ?? '') . ' ' . ($emisor->getApellidos() ?? '')),
                ] : null,
            ];
        }, $mensajes);

        return $this->json($payload);
    }

    // Listar todos los mensajes
    #[Route('/api/mensajes', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        $mensajes = $this->mensajeService->getVisibleMensajesForUser($user);
        return $this->json($mensajes);
    }

    // Obtener mensaje por ID
    #[Route('/api/mensajes/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $mensaje = $this->mensajeService->getMensajeById($id);
        if (!$mensaje) {
            //Mensaje no encontrado
            return $this->json(['error' => 'Mensaje no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user || !$this->mensajeService->canViewMensaje($user, $mensaje)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        return $this->json($mensaje);
    }

    // Actualizar mensaje
    #[Route('/api/mensajes/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $mensaje = $this->mensajeService->getMensajeById($id);
        if (!$mensaje) {
            //Mensaje no encontrado
            return $this->json(['error' => 'Mensaje no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        if (!$this->mensajeService->canEditMensaje($user, $mensaje)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        $data = json_decode($request->getContent(), true);
        $data['id_emisor'] = $mensaje->getEmisor() ? $mensaje->getEmisor()->getId() : null;
        $mensaje = $this->mensajeService->updateMensaje($mensaje, $data);
        return $this->json($mensaje);
    }

    // Eliminar mensaje
    #[Route('/api/mensajes/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $mensaje = $this->mensajeService->getMensajeById($id);
        if (!$mensaje) {
            //Mensaje no encontrado
            return $this->json(['error' => 'Mensaje no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        if (!$this->mensajeService->canEditMensaje($user, $mensaje)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        $this->mensajeService->deleteMensaje($mensaje);
        //Mensaje eliminado correctamente
        return $this->json(['message' => 'Mensaje eliminado correctamente']);
    }
}
