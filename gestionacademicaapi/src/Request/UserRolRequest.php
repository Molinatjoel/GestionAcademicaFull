<?php
namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UserRolRequest
{
    // ID de usuario
    #[Assert\NotNull(message: "El usuario es obligatorio")]
    public ?int $id_user = null;

    // ID de rol
    #[Assert\NotNull(message: "El rol es obligatorio")]
    public ?int $id_rol = null;
}
