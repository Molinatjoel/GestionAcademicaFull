<?php
namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ChatRequest
{
    //Nombre del chat
    #[Assert\NotBlank(message: "El nombre del chat es obligatorio")]
    #[Assert\Length(max: 80, maxMessage: "Máximo 80 caracteres")]
    public ?string $nombre = null;

    //Tipo de chat (grupo, privado, etc)
    #[Assert\NotBlank(message: "El tipo de chat es obligatorio")]
    #[Assert\Length(max: 20, maxMessage: "Máximo 20 caracteres")]
    public ?string $tipo = null;
}
