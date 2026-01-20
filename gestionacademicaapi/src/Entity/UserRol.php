<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_user_rol', columns: ['id_user', 'id_rol'])
])]
class UserRol
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_user_rol = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Rol::class)]
    #[ORM\JoinColumn(name: 'id_rol', referencedColumnName: 'id_rol')]
    private Rol $rol;

    public function getIdUserRol(): ?int
    {
        return $this->id_user_rol;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getRol(): Rol
    {
        return $this->rol;
    }

    public function setRol(Rol $rol): self
    {
        $this->rol = $rol;
        return $this;
    }
}
