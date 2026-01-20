<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PeriodoLectivo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_periodo = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $descripcion;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $fecha_inicio;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $fecha_fin;

    #[ORM\Column(type: 'boolean')]
    private bool $estado;

    public function getIdPeriodo(): ?int
    {
        return $this->id_periodo;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function getFechaInicio(): \DateTimeInterface
    {
        return $this->fecha_inicio;
    }

    public function setFechaInicio(\DateTimeInterface $fecha_inicio): self
    {
        $this->fecha_inicio = $fecha_inicio;
        return $this;
    }

    public function getFechaFin(): \DateTimeInterface
    {
        return $this->fecha_fin;
    }

    public function setFechaFin(\DateTimeInterface $fecha_fin): self
    {
        $this->fecha_fin = $fecha_fin;
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
