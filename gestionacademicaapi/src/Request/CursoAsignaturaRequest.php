<?php
namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CursoAsignaturaRequest
{
    //ID del curso
    #[Assert\NotNull(message: "El curso es obligatorio")]
    public ?int $id_curso = null;

    //ID de la asignatura
    #[Assert\NotNull(message: "La asignatura es obligatoria")]
    public ?int $id_asignatura = null;

    //ID del docente
    #[Assert\NotNull(message: "El docente es obligatorio")]
    public ?int $id_docente = null;

    //Fecha de creación
    public ?\DateTimeInterface $fecha_creacion = null;
}
