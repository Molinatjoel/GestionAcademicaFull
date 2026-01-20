<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Asignatura
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_asignatura = null;

    #[ORM\Column(type: 'string', length: 80)]
    private string $nombre_asignatura;

    #[ORM\Column(type: 'string', length: 120)]
    private ?string $descripcion = null;

    public function getIdAsignatura(): ?int
    {
        return $this->id_asignatura;
    }

    public function getNombreAsignatura(): string
    {
        return $this->nombre_asignatura;
    }

    public function setNombreAsignatura(string $nombre_asignatura): self
    {
        $this->nombre_asignatura = $nombre_asignatura;
        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;
        return $this;
    }
}
