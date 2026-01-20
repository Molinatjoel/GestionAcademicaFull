<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Mensaje
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_mensaje = null;

    #[ORM\ManyToOne(targetEntity: Chat::class)]
    #[ORM\JoinColumn(name: 'id_chat', referencedColumnName: 'id_chat')]
    private Chat $chat;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_emisor', referencedColumnName: 'id')]
    private User $emisor;

    #[ORM\Column(type: 'string', length: 500)]
    private string $contenido;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $fecha_envio;

    public function getIdMensaje(): ?int
    {
        return $this->id_mensaje;
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

    public function getEmisor(): User
    {
        return $this->emisor;
    }

    public function setEmisor(User $emisor): self
    {
        $this->emisor = $emisor;
        return $this;
    }

    public function getContenido(): string
    {
        return $this->contenido;
    }

    public function setContenido(string $contenido): self
    {
        $this->contenido = $contenido;
        return $this;
    }

    public function getFechaEnvio(): \DateTimeInterface
    {
        return $this->fecha_envio;
    }

    public function setFechaEnvio(\DateTimeInterface $fecha_envio): self
    {
        $this->fecha_envio = $fecha_envio;
        return $this;
    }
}
