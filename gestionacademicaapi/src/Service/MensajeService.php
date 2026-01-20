<?php
namespace App\Service;

use App\Entity\Mensaje;
use App\Entity\User;
use App\Interface\MensajeServiceInterface;
use App\Repository\MensajeRepository;
use App\Repository\ChatRepository;
use App\Repository\UserRepository;
use App\Repository\ChatUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MensajeService implements MensajeServiceInterface
{
    private MensajeRepository $mensajeRepository;
    private ChatRepository $chatRepository;
    private UserRepository $userRepository;
    private ChatUserRepository $chatUserRepository;
    private EntityManagerInterface $em;
    private HubInterface $hub;

    public function __construct(
        MensajeRepository $mensajeRepository,
        ChatRepository $chatRepository,
        UserRepository $userRepository,
        ChatUserRepository $chatUserRepository,
        EntityManagerInterface $em,
        HubInterface $hub
    ) {
        $this->mensajeRepository = $mensajeRepository;
        $this->chatRepository = $chatRepository;
        $this->userRepository = $userRepository;
        $this->chatUserRepository = $chatUserRepository;
        $this->em = $em;
        $this->hub = $hub;
    }

    public function createMensaje(array $data): Mensaje
    {
        $mensaje = new Mensaje();
        
        // Asignar chat
        if (isset($data['id_chat'])) {
            $chat = $this->chatRepository->find($data['id_chat']);
            if ($chat) {
                $mensaje->setChat($chat);
            }
        }
        
        // Asignar emisor
        if (isset($data['id_emisor'])) {
            $emisor = $this->userRepository->find($data['id_emisor']);
            if ($emisor) {
                $mensaje->setEmisor($emisor);
            }
        }
        
        // Asignar contenido
        if (isset($data['contenido'])) {
            $mensaje->setContenido($data['contenido']);
        }
        
        // Asignar fecha de envío
        $fechaEnvio = new \DateTime();
        if (isset($data['fecha_envio'])) {
            if ($data['fecha_envio'] instanceof \DateTimeInterface) {
                $fechaEnvio = $data['fecha_envio'];
            } else {
                $fechaEnvio = new \DateTime($data['fecha_envio']);
            }
        }
        $mensaje->setFechaEnvio($fechaEnvio);
        
        $this->em->persist($mensaje);
        $this->em->flush();
        $this->publishMercureUpdate($mensaje, 'mensaje.creado');
        return $mensaje;
    }

    public function updateMensaje(Mensaje $mensaje, array $data): Mensaje
    {
        // Actualizar chat
        if (isset($data['id_chat'])) {
            $chat = $this->chatRepository->find($data['id_chat']);
            if ($chat) {
                $mensaje->setChat($chat);
            }
        }
        
        // Actualizar emisor
        if (isset($data['id_emisor'])) {
            $emisor = $this->userRepository->find($data['id_emisor']);
            if ($emisor) {
                $mensaje->setEmisor($emisor);
            }
        }
        
        // Actualizar contenido
        if (isset($data['contenido'])) {
            $mensaje->setContenido($data['contenido']);
        }
        
        // Actualizar fecha de envío
        if (isset($data['fecha_envio'])) {
            if ($data['fecha_envio'] instanceof \DateTimeInterface) {
                $mensaje->setFechaEnvio($data['fecha_envio']);
            } else {
                $mensaje->setFechaEnvio(new \DateTime($data['fecha_envio']));
            }
        }
        
        $this->em->flush();
        $this->publishMercureUpdate($mensaje, 'mensaje.actualizado');
        return $mensaje;
    }

    public function deleteMensaje(Mensaje $mensaje): void
    {
        $this->publishMercureUpdate($mensaje, 'mensaje.eliminado');
        $this->em->remove($mensaje);
        $this->em->flush();
    }

    public function getMensajeById(int $id): ?Mensaje
    {
        return $this->mensajeRepository->find($id);
    }

    public function getAllMensajes(): array
    {
        return $this->mensajeRepository->findAll();
    }

    public function getVisibleMensajesForUser(User $user): array
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return $this->mensajeRepository->findAll();
        }

        $qb = $this->mensajeRepository->createQueryBuilder('m')
            ->innerJoin('m.chat', 'c')
            ->innerJoin('App\\Entity\\ChatUser', 'cu', 'WITH', 'cu.chat = c.id_chat')
            ->andWhere('cu.user = :uid')
            ->setParameter('uid', $user->getId());

        return $qb->getQuery()->getResult();
    }

    public function canViewMensaje(User $user, Mensaje $mensaje): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        $chat = $mensaje->getChat();
        $chatUser = $this->chatRepository->createQueryBuilder('c')
            ->innerJoin('App\\Entity\\ChatUser', 'cu', 'WITH', 'cu.chat = c.id_chat')
            ->andWhere('c = :chat')
            ->andWhere('cu.user = :uid')
            ->setParameter('chat', $chat)
            ->setParameter('uid', $user->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $chatUser !== null;
    }

    public function canSendMensaje(User $user, array $data): bool
    {
        // Emisor debe ser el usuario autenticado y miembro del chat
        if (!isset($data['id_chat'])) {
            return false;
        }

        if (isset($data['id_emisor']) && (int) $data['id_emisor'] !== $user->getId()) {
            return false;
        }

        $chat = $this->chatRepository->find($data['id_chat']);
        if (!$chat) {
            return false;
        }

        $membership = $this->chatUserRepository->findOneBy([
            'chat' => $chat,
            'user' => $user,
        ]);

        return $membership !== null;
    }

    public function getMensajesDeChat(User $user, int $chatId): array
    {
        $chat = $this->chatRepository->find($chatId);
        if (!$chat) {
            throw new \InvalidArgumentException('Chat no encontrado');
        }

        $membership = $this->chatUserRepository->findOneBy([
            'chat' => $chat,
            'user' => $user,
        ]);

        if (!$membership && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            throw new \RuntimeException('Acceso denegado');
        }

        return $this->mensajeRepository->findBy(
            ['chat' => $chat],
            ['fecha_envio' => 'ASC']
        );
    }

    public function canEditMensaje(User $user, Mensaje $mensaje): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        return $mensaje->getEmisor() && $mensaje->getEmisor()->getId() === $user->getId();
    }

    private function publishMercureUpdate(Mensaje $mensaje, string $event): void
    {
        $chat = $mensaje->getChat();
        if (!$chat || !$chat->getIdChat()) {
            return;
        }

        $emisor = $mensaje->getEmisor();
        $payload = [
            'type' => $event,
            'chat_id' => $chat->getIdChat(),
            'mensaje' => [
                'id_mensaje' => $mensaje->getIdMensaje(),
                'contenido' => $mensaje->getContenido(),
                'fecha_envio' => $mensaje->getFechaEnvio()?->format('c'),
                'emisor' => $emisor ? [
                    'id' => $emisor->getId(),
                    'nombre' => trim(($emisor->getNombres() ?? '') . ' ' . ($emisor->getApellidos() ?? '')),
                ] : null,
            ],
        ];

        $topic = '/chats/' . $chat->getIdChat();
        $update = new Update($topic, json_encode($payload, JSON_UNESCAPED_UNICODE));
        $this->hub->publish($update);
    }
}
