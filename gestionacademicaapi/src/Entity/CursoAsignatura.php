<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CursoAsignatura
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_curso_asignatura = null;

    #[ORM\ManyToOne(targetEntity: Curso::class)]
    #[ORM\JoinColumn(name: 'id_curso', referencedColumnName: 'id_curso')]
    private Curso $curso;

    #[ORM\ManyToOne(targetEntity: Asignatura::class)]
    #[ORM\JoinColumn(name: 'id_asignatura', referencedColumnName: 'id_asignatura')]
    private Asignatura $asignatura;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_docente', referencedColumnName: 'id')]
    private User $docente;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $fecha_creacion;

    public function getIdCursoAsignatura(): ?int
    {
        return $this->id_curso_asignatura;
    }

    public function getCurso(): Curso
    {
        return $this->curso;
    }

    public function setCurso(Curso $curso): self
    {
        $this->curso = $curso;
        return $this;
    }

    public function getAsignatura(): Asignatura
    {
        return $this->asignatura;
    }

    public function setAsignatura(Asignatura $asignatura): self
    {
        $this->asignatura = $asignatura;
        return $this;
    }

    public function getDocente(): User
    {
        return $this->docente;
    }

    public function setDocente(User $docente): self
    {
        $this->docente = $docente;
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
