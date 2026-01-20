<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_chat_user', columns: ['id_chat', 'id_user'])
])]
class ChatUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_chat_user = null;

    #[ORM\ManyToOne(targetEntity: Chat::class)]
    #[ORM\JoinColumn(name: 'id_chat', referencedColumnName: 'id_chat')]
    private Chat $chat;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id')]
    private User $user;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $fecha_union;

    public function getIdChatUser(): ?int
    {
        return $this->id_chat_user;
    }

    public function getChat(): Chat
    {
        return $this->chat;
    }

    public function setChat(Chat $chat): self
    {
        $this->chat = $chat;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getFechaUnion(): \DateTimeInterface
    {
        return $this->fecha_union;
    }

    public function setFechaUnion(\DateTimeInterface $fecha_union): self
    {
        $this->fecha_union = $fecha_union;
        return $this;
    }
}
