<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Matricula
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_matricula = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_estudiante', referencedColumnName: 'id')]
    private User $estudiante;

    #[ORM\ManyToOne(targetEntity: Curso::class)]
    #[ORM\JoinColumn(name: 'id_curso', referencedColumnName: 'id_curso')]
    private Curso $curso;

    #[ORM\ManyToOne(targetEntity: PeriodoLectivo::class)]
    #[ORM\JoinColumn(name: 'id_periodo', referencedColumnName: 'id_periodo')]
    private PeriodoLectivo $periodo;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $fecha_matricula;

    #[ORM\Column(type: 'boolean')]
    private bool $estado;

    public function getIdMatricula(): ?int
    {
        return $this->id_matricula;
    }

    public function getEstudiante(): User
    {
        return $this->estudiante;
    }

    public function setEstudiante(User $estudiante): self
    {
        $this->estudiante = $estudiante;
        return $this;
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

    public function getPeriodo(): PeriodoLectivo
    {
        return $this->periodo;
    }

    public function setPeriodo(PeriodoLectivo $periodo): self
    {
        $this->periodo = $periodo;
        return $this;
    }

    public function getFechaMatricula(): \DateTimeInterface
    {
        return $this->fecha_matricula;
    }

    public function setFechaMatricula(\DateTimeInterface $fecha_matricula): self
    {
        $this->fecha_matricula = $fecha_matricula;
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
