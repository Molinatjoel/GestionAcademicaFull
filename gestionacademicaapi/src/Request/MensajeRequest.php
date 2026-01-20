<?php
namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class MensajeRequest
{
    //ID del chat
    #[Assert\NotNull(message: "El chat es obligatorio")]
    public ?int $id_chat = null;

    //ID del usuario que envía (emisor)
    #[Assert\NotNull(message: "El emisor es obligatorio")]
    public ?int $id_emisor = null;

    //Contenido del mensaje
    #[Assert\NotBlank(message: "El contenido es obligatorio")]
    #[Assert\Length(max: 500, maxMessage: "Máximo 500 caracteres")]
    public ?string $contenido = null;
}
