<?php
namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UserRequest
{
    //Nombre del usuario
    #[Assert\NotBlank(message: "El nombre es obligatorio")]
    #[Assert\Length(max: 80, maxMessage: "Máximo 80 caracteres")]
    public string $nombres;

    //Apellido del usuario
    #[Assert\NotBlank(message: "El apellido es obligatorio")]
    #[Assert\Length(max: 80, maxMessage: "Máximo 80 caracteres")]
    public string $apellidos;

    //Fecha de nacimiento
    #[Assert\DateTime(message: "Fecha de nacimiento inválida")]
    public ?\DateTimeInterface $fecha_nacimiento = null;

    //Dirección
    #[Assert\NotBlank(message: "La dirección es obligatoria")]
    #[Assert\Length(max: 120, maxMessage: "Máximo 120 caracteres")]
    public string $direccion;

    //Correo electrónico
    #[Assert\NotBlank(message: "El correo es obligatorio")]
    #[Assert\Email(message: "Correo electrónico inválido")]
    #[Assert\Length(max: 120, maxMessage: "Máximo 120 caracteres")]
    public string $correo;

    //Teléfono
    #[Assert\NotBlank(message: "El teléfono es obligatorio")]
    #[Assert\Length(max: 20, maxMessage: "Máximo 20 caracteres")]
    public string $telefono;

    //Contraseña
    #[Assert\Length(min: 8, max: 255, minMessage: "Mínimo 8 caracteres", maxMessage: "Máximo 255 caracteres")]
    public ?string $contrasena = null;

    //Estado
    public bool $estado;

    //Fechas de auditoría
    public ?\DateTimeInterface $fecha_creacion;
    public ?\DateTimeInterface $fecha_actualizacion;
}
