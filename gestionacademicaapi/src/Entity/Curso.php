<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Curso
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_curso = null;

    #[ORM\Column(type: 'string', length: 60)]
    private string $nombre_curso;

    #[ORM\Column(type: 'string', length: 30)]
    private string $nivel;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_docente_titular', referencedColumnName: 'id', nullable: true)]
    private ?User $docenteTitular = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $fecha_creacion;

    #[ORM\Column(type: 'boolean')]
    private bool $estado;

    public function getIdCurso(): ?int
    {
        return $this->id_curso;
    }

    public function getNombreCurso(): string
    {
        return $this->nombre_curso;
    }

    public function setNombreCurso(string $nombre_curso): self
    {
        $this->nombre_curso = $nombre_curso;
        return $this;
    }

    public function getNivel(): string
    {
        return $this->nivel;
    }

    public function setNivel(string $nivel): self
    {
        $this->nivel = $nivel;
        return $this;
    }

    public function getDocenteTitular(): ?User
    {
        return $this->docenteTitular;
    }

    public function setDocenteTitular(?User $docenteTitular): self
    {
        $this->docenteTitular = $docenteTitular;
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

    public function isEstado(): bool
    {
        return $this->estado;
    }

    public function setEstado(bool $estado): self
    {
        $this->estado = $estado;
        return $this;
    }
}
