<?php
namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class PeriodoLectivoRequest
{
    //Descripción del periodo lectivo
    #[Assert\NotBlank(message: "La descripción es obligatoria")]
    #[Assert\Length(max: 80, maxMessage: "Máximo 80 caracteres")]
    public ?string $descripcion = null;

    //Fecha de inicio
    #[Assert\NotBlank(message: "La fecha de inicio es obligatoria")]
    #[Assert\DateTime(message: "Formato de fecha inválido")]
    public ?\DateTimeInterface $fecha_inicio = null;

    //Fecha de fin
    #[Assert\NotBlank(message: "La fecha de fin es obligatoria")]
    #[Assert\DateTime(message: "Formato de fecha inválido")]
    public ?\DateTimeInterface $fecha_fin = null;

    //Estado
    public ?bool $estado = true;

}
