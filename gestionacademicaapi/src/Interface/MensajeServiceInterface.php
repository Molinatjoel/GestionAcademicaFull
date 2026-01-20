<?php
namespace App\Interface;

use App\Entity\Mensaje;

interface MensajeServiceInterface
{
    public function createMensaje(array $data): Mensaje;
    public function updateMensaje(Mensaje $mensaje, array $data): Mensaje;
    public function deleteMensaje(Mensaje $mensaje): void;
    public function getMensajeById(int $id): ?Mensaje;
    public function getAllMensajes(): array;
}
