<?php
namespace App\Controller;

use App\Service\ChatService;
use App\Request\ChatRequest;
use App\Entity\Chat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChatController extends AbstractController
{
    private ChatService $chatService;
    private ValidatorInterface $validator;
    private Security $security;

    // Inyección del servicio ChatService y el validador
    public function __construct(ChatService $chatService, ValidatorInterface $validator, Security $security)
    {
        $this->chatService = $chatService;
        $this->validator = $validator;
        $this->security = $security;
    }

    // Crear chat
    #[Route('/api/chats', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        $roles = $user->getRoles();
        $data = json_decode($request->getContent(), true);
        $data['id_creador'] = $user->getId();

        $tipo = $data['tipo'] ?? null;
        try {
            if ($tipo === 'curso') {
                $chat = $this->chatService->ensureGroupChatForCurso((int) ($data['id_curso'] ?? 0), $user);
            } elseif ($tipo === 'privado') {
                $chat = $this->chatService->ensurePrivateChat($user, (int) ($data['id_usuario_destino'] ?? 0));
            } else {
                // Solo admin/docente para chats libres
                if (!in_array('ROLE_ADMIN', $roles, true) && !in_array('ROLE_DOCENTE', $roles, true)) {
                    return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
                }
                $chatRequest = new ChatRequest();
                $chatRequest->nombre = $data['nombre'] ?? null;
                $chatRequest->tipo = $data['tipo'] ?? 'general';
                $errors = $this->validator->validate($chatRequest);
                if (count($errors) > 0) {
                    $errorMessages = [];
                    foreach ($errors as $error) {
                        $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                    }
                    return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
                }
                $chat = $this->chatService->createChat($data);
            }
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return $this->json($this->serializeChat($chat));
    }

    // Listar todos los chats
    #[Route('/api/chats', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        $chats = $this->chatService->getVisibleChatsForUser($user);
        $payload = array_map(fn (Chat $c) => $this->serializeChat($c), $chats);
        return $this->json($payload);
    }

    #[Route('/api/chats/buscar-usuarios', methods: ['GET'])]
    public function searchUsers(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }

        $term = (string) $request->query->get('q', '');
        $cursoId = $request->query->getInt('id_curso', 0) ?: null;

        try {
            $results = $this->chatService->searchUsersForChat($user, $term, $cursoId);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($results);
    }

    // Obtener chat por ID
    #[Route('/api/chats/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $chat = $this->chatService->getChatById($id);
        if (!$chat) {
            //Chat no encontrado
            return $this->json(['error' => 'Chat no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user || !$this->chatService->canViewChat($user, $chat)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        return $this->json($this->serializeChat($chat));
    }

    // Actualizar chat
    #[Route('/api/chats/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $chat = $this->chatService->getChatById($id);
        if (!$chat) {
            //Chat no encontrado
            return $this->json(['error' => 'Chat no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        $roles = $user->getRoles();
        if (!in_array('ROLE_ADMIN', $roles, true) && !in_array('ROLE_DOCENTE', $roles, true)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        $data = json_decode($request->getContent(), true);
        $chat = $this->chatService->updateChat($chat, $data);
        return $this->json($this->serializeChat($chat));
    }

    // Eliminar chat
    #[Route('/api/chats/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $chat = $this->chatService->getChatById($id);
        if (!$chat) {
            //Chat no encontrado
            return $this->json(['error' => 'Chat no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        $roles = $user->getRoles();
        if (!in_array('ROLE_ADMIN', $roles, true) && !in_array('ROLE_DOCENTE', $roles, true)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        $this->chatService->deleteChat($chat);
        //Chat eliminado correctamente
        return $this->json(['message' => 'Chat eliminado correctamente']);
    }

    private function serializeChat(Chat $chat): array
    {
        return [
            'id_chat' => $chat->getIdChat(),
            'titulo' => $chat->getTitulo(),
            'tipo' => $chat->getTipo(),
            'id_curso' => $chat->getCurso()?->getIdCurso(),
            'curso' => $chat->getCurso()?->getNombreCurso(),
            'id_creador' => $chat->getCreador()?->getId(),
            'fecha_creacion' => $chat->getFechaCreacion()?->format('c'),
            'participantes' => $this->chatService->getParticipants($chat),
        ];
    }
}
