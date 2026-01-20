<?php
namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class DatosFamiliaresRequest
{
    //ID del estudiante
    #[Assert\NotNull(message: "El estudiante es obligatorio")]
    public ?int $id_estudiante = null;

    //ID de usuario representante (opcional)
    public ?int $id_representante_user = null;

    //Nombre del padre
    #[Assert\Length(max: 80, maxMessage: "Máximo 80 caracteres")]
    public ?string $nombre_padre = null;

    //Teléfono del padre
    #[Assert\Length(max: 20, maxMessage: "Máximo 20 caracteres")]
    public ?string $telefono_padre = null;

    //Nombre de la madre
    #[Assert\Length(max: 80, maxMessage: "Máximo 80 caracteres")]
    public ?string $nombre_madre = null;

    //Teléfono de la madre
    #[Assert\Length(max: 20, maxMessage: "Máximo 20 caracteres")]
    public ?string $telefono_madre = null;

    //Dirección familiar
    #[Assert\Length(max: 120, maxMessage: "Máximo 120 caracteres")]
    public ?string $direccion_familiar = null;

    //Parentesco representante
    #[Assert\Length(max: 50, maxMessage: "Máximo 50 caracteres")]
    public ?string $parentesco_representante = null;

    //Nombre representante
    #[Assert\Length(max: 80, maxMessage: "Máximo 80 caracteres")]
    public ?string $nombre_representante = null;

    //Ocupación representante
    #[Assert\Length(max: 80, maxMessage: "Máximo 80 caracteres")]
    public ?string $ocupacion_representante = null;

    //Teléfono representante
    #[Assert\Length(max: 20, maxMessage: "Máximo 20 caracteres")]
    public ?string $telefono_representante = null;
}
