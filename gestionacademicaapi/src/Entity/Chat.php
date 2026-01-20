<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Curso;
use App\Entity\User;

#[ORM\Entity]
class Chat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_chat = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $titulo;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'general'])]
    private string $tipo = 'general';

    #[ORM\ManyToOne(targetEntity: Curso::class)]
    #[ORM\JoinColumn(name: 'id_curso', referencedColumnName: 'id_curso', nullable: true, onDelete: 'SET NULL')]
    private ?Curso $curso = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_creador', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $creador = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $fecha_creacion;

    public function getIdChat(): ?int
    {
        return $this->id_chat;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;
        return $this;
    }

    public function getCurso(): ?Curso
    {
        return $this->curso;
    }

    public function setCurso(?Curso $curso): self
    {
        $this->curso = $curso;
        return $this;
    }

    public function getCreador(): ?User
    {
        return $this->creador;
    }

    public function setCreador(?User $creador): self
    {
        $this->creador = $creador;
        return $this;
    }

    public function getFechaCreacion(): \DateTimeInterface
    {
        return $this->fecha_creacion;
    }

    public function setFechaCreacion(\DateTimeInterface $fecha_creacion): self
    {
        $this->fecha_creacion = $fecha_creacion;
        return $this;
    }
}
