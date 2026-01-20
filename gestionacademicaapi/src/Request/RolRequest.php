<?php
namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class RolRequest
{
    //Nombre del rol
    #[Assert\NotBlank(message: "El nombre del rol es obligatorio")]
    #[Assert\Length(max: 50, maxMessage: "Máximo 50 caracteres")]
    public string $nombre_rol;

    //Descripción del rol
    #[Assert\Length(max: 120, maxMessage: "Máximo 120 caracteres")]
    public ?string $descripcion = null;
}
