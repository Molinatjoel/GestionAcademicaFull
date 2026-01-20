<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Calificacion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_calificacion = null;

    #[ORM\ManyToOne(targetEntity: Matricula::class)]
    #[ORM\JoinColumn(name: 'id_matricula', referencedColumnName: 'id_matricula')]
    private Matricula $matricula;

    #[ORM\ManyToOne(targetEntity: CursoAsignatura::class)]
    #[ORM\JoinColumn(name: 'id_curso_asignatura', referencedColumnName: 'id_curso_asignatura')]
    private CursoAsignatura $cursoAsignatura;

    #[ORM\Column(type: 'decimal', precision: 4, scale: 2)]
    private float $nota;

    #[ORM\Column(type: 'string', length: 150)]
    private ?string $observacion = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $fecha_registro;

    public function getIdCalificacion(): ?int
    {
        return $this->id_calificacion;
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

    public function getNota(): float
    {
        return $this->nota;
    }

    public function setNota(float $nota): self
    {
        $this->nota = $nota;
        return $this;
    }

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(?string $observacion): self
    {
        $this->observacion = $observacion;
        return $this;
    }

    public function getFechaRegistro(): \DateTimeInterface
    {
        return $this->fecha_registro;
    }

    public function setFechaRegistro(\DateTimeInterface $fecha_registro): self
    {
        $this->fecha_registro = $fecha_registro;
        return $this;
    }
}
