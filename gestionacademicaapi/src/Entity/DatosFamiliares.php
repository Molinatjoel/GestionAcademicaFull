<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DatosFamiliares
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_datos_familiares = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_estudiante', referencedColumnName: 'id')]
    private User $estudiante;

    #[ORM\Column(type: 'string', length: 80)]
    private string $nombre_padre;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $telefono_padre = null;

    #[ORM\Column(type: 'string', length: 80)]
    private string $nombre_madre;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $telefono_madre = null;

    #[ORM\Column(type: 'string', length: 120)]
    private ?string $direccion_familiar = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $parentesco_representante = null;

    #[ORM\Column(type: 'string', length: 80)]
    private ?string $nombre_representante = null;

    #[ORM\Column(type: 'string', length: 80)]
    private ?string $ocupacion_representante = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $telefono_representante = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_representante_user', referencedColumnName: 'id', nullable: true)]
    private ?User $representanteUser = null;

    public function getIdDatosFamiliares(): ?int
    {
        return $this->id_datos_familiares;
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

    public function getNombrePadre(): string
    {
        return $this->nombre_padre;
    }

    public function setNombrePadre(string $nombre_padre): self
    {
        $this->nombre_padre = $nombre_padre;
        return $this;
    }

    public function getTelefonoPadre(): ?string
    {
        return $this->telefono_padre;
    }

    public function setTelefonoPadre(?string $telefono_padre): self
    {
        $this->telefono_padre = $telefono_padre;
        return $this;
    }

    public function getNombreMadre(): string
    {
        return $this->nombre_madre;
    }

    public function setNombreMadre(string $nombre_madre): self
    {
        $this->nombre_madre = $nombre_madre;
        return $this;
    }

    public function getTelefonoMadre(): ?string
    {
        return $this->telefono_madre;
    }

    public function setTelefonoMadre(?string $telefono_madre): self
    {
        $this->telefono_madre = $telefono_madre;
        return $this;
    }

    public function getDireccionFamiliar(): ?string
    {
        return $this->direccion_familiar;
    }

    public function setDireccionFamiliar(?string $direccion_familiar): self
    {
        $this->direccion_familiar = $direccion_familiar;
        return $this;
    }

    public function getParentescoRepresentante(): ?string
    {
        return $this->parentesco_representante;
    }

    public function setParentescoRepresentante(?string $parentesco_representante): self
    {
        $this->parentesco_representante = $parentesco_representante;
        return $this;
    }

    public function getNombreRepresentante(): ?string
    {
        return $this->nombre_representante;
    }

    public function setNombreRepresentante(?string $nombre_representante): self
    {
        $this->nombre_representante = $nombre_representante;
        return $this;
    }

    public function getOcupacionRepresentante(): ?string
    {
        return $this->ocupacion_representante;
    }

    public function setOcupacionRepresentante(?string $ocupacion_representante): self
    {
        $this->ocupacion_representante = $ocupacion_representante;
        return $this;
    }

    public function getTelefonoRepresentante(): ?string
    {
        return $this->telefono_representante;
    }

    public function setTelefonoRepresentante(?string $telefono_representante): self
    {
        $this->telefono_representante = $telefono_representante;
        return $this;
    }

    public function getRepresentanteUser(): ?User
    {
        return $this->representanteUser;
    }

    public function setRepresentanteUser(?User $representanteUser): self
    {
        $this->representanteUser = $representanteUser;
        return $this;
    }
}
