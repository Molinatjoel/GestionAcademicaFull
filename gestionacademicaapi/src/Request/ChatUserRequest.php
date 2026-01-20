<?php
namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ChatUserRequest
{
    //ID del chat
    #[Assert\NotNull(message: "El chat es obligatorio")]
    public ?int $id_chat = null;

    //ID del usuario
    #[Assert\NotNull(message: "El usuario es obligatorio")]
    public ?int $id_usuario = null;

    //Rol en el chat (admin, miembro, etc)
    #[Assert\Length(max: 20, maxMessage: "Máximo 20 caracteres")]
    public ?string $rol = null;
}
