<?php
namespace App\Interface;

use App\Entity\Asignatura;

interface AsignaturaServiceInterface
{
    public function createAsignatura(array $data): Asignatura;
    public function updateAsignatura(Asignatura $asignatura, array $data): Asignatura;
    public function deleteAsignatura(Asignatura $asignatura): void;
    public function getAsignaturaById(int $id): ?Asignatura;
    public function getAllAsignaturas(): array;
}
