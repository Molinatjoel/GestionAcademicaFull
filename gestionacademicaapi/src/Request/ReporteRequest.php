<?php
namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ReporteRequest
{
    //Título
    #[Assert\NotBlank(message: "El título es obligatorio")]
    #[Assert\Length(max: 100, maxMessage: "Máximo 100 caracteres")]
    public string $titulo;

    //Descripción
    #[Assert\NotBlank(message: "La descripción es obligatoria")]
    public string $descripcion;

    //Tipo
    #[Assert\NotBlank(message: "El tipo es obligatorio")]
    #[Assert\Length(max: 40, maxMessage: "Máximo 40 caracteres")]
    public string $tipo;

    //ID del curso
    #[Assert\NotNull(message: "El curso es obligatorio")]
    public ?int $id_curso = null;

    //ID del docente
    #[Assert\NotNull(message: "El docente es obligatorio")]
    public ?int $id_docente = null;

    //ID del periodo
    #[Assert\NotNull(message: "El periodo lectivo es obligatorio")]
    public ?int $id_periodo = null;

    //Fecha de creación
    public ?\DateTimeInterface $fecha_creacion = null;
}
