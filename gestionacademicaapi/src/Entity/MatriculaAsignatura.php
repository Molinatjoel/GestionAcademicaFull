<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'matricula_asignatura')]
#[ORM\UniqueConstraint(name: 'uniq_matricula_curso_asignatura', columns: ['id_matricula', 'id_curso_asignatura'])]
class MatriculaAsignatura
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_matricula_asignatura = null;

    #[ORM\ManyToOne(targetEntity: Matricula::class)]
    #[ORM\JoinColumn(name: 'id_matricula', referencedColumnName: 'id_matricula', nullable: false, onDelete: 'CASCADE')]
    private Matricula $matricula;

    #[ORM\ManyToOne(targetEntity: CursoAsignatura::class)]
    #[ORM\JoinColumn(name: 'id_curso_asignatura', referencedColumnName: 'id_curso_asignatura', nullable: false, onDelete: 'CASCADE')]
    private CursoAsignatura $cursoAsignatura;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $fecha_asignacion;

    public function getIdMatriculaAsignatura(): ?int
    {
        return $this->id_matricula_asignatura;
    }

    public function getMatricula(): Matricula
    {
        return $this->matricula;
    }

    public function setMatricula(Matricula $matricula): self
    {
        $this->matricula = $matricula;
        return $this;
    }

    public function getCursoAsignatura(): CursoAsignatura
    {
        return $this->cursoAsignatura;
    }

    public function setCursoAsignatura(CursoAsignatura $cursoAsignatura): self
    {
        $this->cursoAsignatura = $cursoAsignatura;
        return $this;
    }

    public function getFechaAsignacion(): \DateTimeInterface
    {
        return $this->fecha_asignacion;
    }

    public function setFechaAsignacion(\DateTimeInterface $fecha_asignacion): self
    {
        $this->fecha_asignacion = $fecha_asignacion;
        return $this;
    }
}
