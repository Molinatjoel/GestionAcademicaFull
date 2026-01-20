<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_nombre_rol', columns: ['nombre_rol'])
])]
class Rol
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_rol = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $nombre_rol;

    #[ORM\Column(type: 'string', length: 120)]
    private ?string $descripcion = null;

    public function getIdRol(): ?int
    {
        return $this->id_rol;
    }

    public function getNombreRol(): string
    {
        return $this->nombre_rol;
    }

    public function setNombreRol(string $nombre_rol): self
    {
        $this->nombre_rol = $nombre_rol;
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
