<?php
namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CursoRequest
{
    //Nombre del curso
    #[Assert\NotBlank(message: "El nombre del curso es obligatorio")]
    #[Assert\Length(max: 60, maxMessage: "Máximo 60 caracteres")]
    public string $nombre_curso;

    //Nivel
    #[Assert\NotBlank(message: "El nivel es obligatorio")]
    #[Assert\Length(max: 30, maxMessage: "Máximo 30 caracteres")]
    public string $nivel;

    //ID del docente titular
    public ?int $id_docente_titular = null;

    //Fecha de creación
    public ?\DateTimeInterface $fecha_creacion = null;

    //Estado
    public bool $estado = true;
}
