<?php
namespace App\Service;

use App\Entity\ChatUser;
use App\Entity\User;
use App\Interface\ChatUserServiceInterface;
use App\Repository\ChatUserRepository;
use App\Repository\ChatRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class ChatUserService implements ChatUserServiceInterface
{
    private ChatUserRepository $chatUserRepository;
    private ChatRepository $chatRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $em;

    public function __construct(
        ChatUserRepository $chatUserRepository,
        ChatRepository $chatRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ) {
        $this->chatUserRepository = $chatUserRepository;
        $this->chatRepository = $chatRepository;
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    public function createChatUser(array $data): ChatUser
    {
        $chatUser = new ChatUser();
        
        // Asignar chat
        if (isset($data['id_chat'])) {
            $chat = $this->chatRepository->find($data['id_chat']);
            if ($chat) {
                $chatUser->setChat($chat);
            }
        }
        
        // Asignar usuario
        if (isset($data['id_user'])) {
            $user = $this->userRepository->find($data['id_user']);
            if ($user) {
                $chatUser->setUser($user);
            }
        }
        
        // Asignar fecha de unión
        $fechaUnion = new \DateTime();
        if (isset($data['fecha_union'])) {
            if ($data['fecha_union'] instanceof \DateTimeInterface) {
                $fechaUnion = $data['fecha_union'];
            } else {
                $fechaUnion = new \DateTime($data['fecha_union']);
            }
        }
        $chatUser->setFechaUnion($fechaUnion);
        
        $this->em->persist($chatUser);
        $this->em->flush();
        return $chatUser;
    }

    public function updateChatUser(ChatUser $chatUser, array $data): ChatUser
    {
        // Actualizar chat
        if (isset($data['id_chat'])) {
            $chat = $this->chatRepository->find($data['id_chat']);
            if ($chat) {
                $chatUser->setChat($chat);
            }
        }
        
        // Actualizar usuario
        if (isset($data['id_user'])) {
            $user = $this->userRepository->find($data['id_user']);
            if ($user) {
                $chatUser->setUser($user);
            }
        }
        
        // Actualizar fecha de unión
        if (isset($data['fecha_union'])) {
            if ($data['fecha_union'] instanceof \DateTimeInterface) {
                $chatUser->setFechaUnion($data['fecha_union']);
            } else {
                $chatUser->setFechaUnion(new \DateTime($data['fecha_union']));
            }
        }
        
        $this->em->flush();
        return $chatUser;
    }

    public function deleteChatUser(ChatUser $chatUser): void
    {
        $this->em->remove($chatUser);
        $this->em->flush();
    }

    public function getChatUserById(int $id): ?ChatUser
    {
        return $this->chatUserRepository->find($id);
    }

    public function getAllChatUsers(): array
    {
        return $this->chatUserRepository->findAll();
    }

    public function getVisibleChatUsersForUser(User $user): array
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return $this->chatUserRepository->findAll();
        }

        // Solo ver los chat-user donde el usuario participa
        return $this->chatUserRepository->findBy(['user' => $user]);
    }

    public function canViewChatUser(User $user, ChatUser $chatUser): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        return $chatUser->getUser()->getId() === $user->getId();
    }
}
