<?php
namespace App\Controller;

use App\Service\ChatUserService;
use App\Request\ChatUserRequest;
use App\Entity\ChatUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChatUserController extends AbstractController
{
    private ChatUserService $chatUserService;
    private ValidatorInterface $validator;
    private Security $security;

    // Inyección del servicio ChatUserService y el validador
    public function __construct(ChatUserService $chatUserService, ValidatorInterface $validator, Security $security)
    {
        $this->chatUserService = $chatUserService;
        $this->validator = $validator;
        $this->security = $security;
    }

    // Crear chat user
    #[Route('/api/chat-usuarios', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        // Normalizar nombre de campo esperado por el servicio
        if (!isset($data['id_user']) && isset($data['id_usuario'])) {
            $data['id_user'] = $data['id_usuario'];
        }
        $chatUserRequest = new ChatUserRequest();
        $chatUserRequest->id_chat = $data['id_chat'] ?? null;
        $chatUserRequest->id_usuario = $data['id_usuario'] ?? $data['id_user'] ?? null;
        $chatUserRequest->rol = $data['rol'] ?? null;
        $errors = $this->validator->validate($chatUserRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            //Errores de validación
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $chatUser = $this->chatUserService->createChatUser($data);
        return $this->json($chatUser);
    }

    // Listar todos los chat users
    #[Route('/api/chat-usuarios', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        $chatUsers = $this->chatUserService->getVisibleChatUsersForUser($user);
        return $this->json($chatUsers);
    }

    // Obtener chat user por ID
    #[Route('/api/chat-usuarios/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $chatUser = $this->chatUserService->getChatUserById($id);
        if (!$chatUser) {
            //ChatUser no encontrado
            return $this->json(['error' => 'ChatUser no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user || !$this->chatUserService->canViewChatUser($user, $chatUser)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        return $this->json($chatUser);
    }

    // Actualizar chat user
    #[Route('/api/chat-usuarios/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $chatUser = $this->chatUserService->getChatUserById($id);
        if (!$chatUser) {
            //ChatUser no encontrado
            return $this->json(['error' => 'ChatUser no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        $data = json_decode($request->getContent(), true);
        if (!isset($data['id_user']) && isset($data['id_usuario'])) {
            $data['id_user'] = $data['id_usuario'];
        }
        $chatUser = $this->chatUserService->updateChatUser($chatUser, $data);
        return $this->json($chatUser);
    }

    // Eliminar chat user
    #[Route('/api/chat-usuarios/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $chatUser = $this->chatUserService->getChatUserById($id);
        if (!$chatUser) {
            //ChatUser no encontrado
            return $this->json(['error' => 'ChatUser no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        $this->chatUserService->deleteChatUser($chatUser);
        //ChatUser eliminado correctamente
        return $this->json(['message' => 'ChatUser eliminado correctamente']);
    }
}
