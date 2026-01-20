<?php
namespace App\Interface;

use App\Entity\Matricula;

interface MatriculaServiceInterface
{
    public function createMatricula(array $data): Matricula;
    public function updateMatricula(Matricula $matricula, array $data): Matricula;
    public function deleteMatricula(Matricula $matricula): void;
    public function getMatriculaById(int $id): ?Matricula;
    public function getAllMatriculas(): array;
}
