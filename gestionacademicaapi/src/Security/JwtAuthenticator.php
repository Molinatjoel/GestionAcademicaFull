<?php
namespace App\Security;

use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class JwtAuthenticator extends AbstractAuthenticator
{
    private UserRepository $userRepository;
    private string $jwtSecret;

    public function __construct(UserRepository $userRepository, string $jwtSecret)
    {
        $this->userRepository = $userRepository;
        $this->jwtSecret = $jwtSecret;
    }

    public function supports(Request $request): ?bool
    {
        $authHeader = $request->headers->get('Authorization');

        return $authHeader && str_starts_with($authHeader, 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new CustomUserMessageAuthenticationException('Falta el token Bearer');
        }

        $token = trim(substr($authHeader, 7));

        try {
            $payload = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            // Persistir el payload para RoleMiddleware (se usa fuera del token de seguridad)
            $request->attributes->set('jwt_payload', $payload);
        } catch (\Throwable $e) {
            throw new CustomUserMessageAuthenticationException('Token inválido o expirado');
        }

        $userIdentifier = $payload->sub ?? null;

        if (!$userIdentifier) {
            throw new CustomUserMessageAuthenticationException('Token sin usuario');
        }

        $rolesFromToken = isset($payload->roles) && is_array($payload->roles) ? $payload->roles : [];

        return new SelfValidatingPassport(
            new UserBadge((string) $userIdentifier, function (string $identifier) use ($rolesFromToken) {
                $user = $this->userRepository->findOneBy(['correo' => $identifier]);

                if (!$user || !$user->isEstado()) {
                    throw new CustomUserMessageAuthenticationException('Usuario no encontrado o inactivo');
                }

                $normalizedRoles = array_map(
                    static fn ($role) => str_starts_with($role, 'ROLE_') ? $role : 'ROLE_' . strtoupper((string) $role),
                    $rolesFromToken
                );

                $user->setRoles($normalizedRoles);

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null; // Continuar la petición normal
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'error' => $exception->getMessageKey(),
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new JsonResponse([
            'error' => 'Autenticación requerida',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
