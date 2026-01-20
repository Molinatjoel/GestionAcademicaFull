<?php
namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class MatriculaRequest
{
    //ID del estudiante
    #[Assert\NotNull(message: "El estudiante es obligatorio")]
    public ?int $id_estudiante = null;

    //ID del curso
    #[Assert\NotNull(message: "El curso es obligatorio")]
    public ?int $id_curso = null;

    //ID del periodo
    #[Assert\NotNull(message: "El periodo lectivo es obligatorio")]
    public ?int $id_periodo = null;

    //Fecha de matrícula
    #[Assert\NotNull(message: "La fecha de matrícula es obligatoria")]
    public ?\DateTimeInterface $fecha_matricula = null;

    //Estado
    public bool $estado = true;
}
