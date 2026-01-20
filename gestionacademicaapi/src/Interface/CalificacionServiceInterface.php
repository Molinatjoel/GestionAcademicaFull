<?php
namespace App\Interface;

use App\Entity\Calificacion;

interface CalificacionServiceInterface
{
    public function createCalificacion(array $data): Calificacion;
    public function updateCalificacion(Calificacion $calificacion, array $data): Calificacion;
    public function deleteCalificacion(Calificacion $calificacion): void;
    public function getCalificacionById(int $id): ?Calificacion;
    public function getAllCalificaciones(): array;
}
