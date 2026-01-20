<?php
namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class AsignaturaRequest
{
    //Nombre de la asignatura
    #[Assert\NotBlank(message: "El nombre de la asignatura es obligatorio")]
    #[Assert\Length(max: 80, maxMessage: "Máximo 80 caracteres")]
    public string $nombre_asignatura;

    //Descripción
    #[Assert\Length(max: 120, maxMessage: "Máximo 120 caracteres")]
    public ?string $descripcion = null;
}
