<?php
namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CalificacionRequest
{
    //ID de matrícula
    #[Assert\NotNull(message: "La matrícula es obligatoria")]
    public ?int $id_matricula = null;

    //ID de curso-asignatura
    #[Assert\NotNull(message: "La relación curso-asignatura es obligatoria")]
    public ?int $id_curso_asignatura = null;

    //Nota
    #[Assert\NotNull(message: "La nota es obligatoria")]
    #[Assert\Range(min: 0, max: 10, notInRangeMessage: "La nota debe estar entre 0 y 10")]
    public ?float $nota = null;

    //Observación
    #[Assert\Length(max: 150, maxMessage: "Máximo 150 caracteres")]
    public ?string $observacion = null;

    //Fecha de registro
    public ?\DateTimeInterface $fecha_registro = null;
}
