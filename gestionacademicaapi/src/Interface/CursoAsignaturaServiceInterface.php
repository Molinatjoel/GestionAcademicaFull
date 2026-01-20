<?php
namespace App\Interface;

use App\Entity\CursoAsignatura;

interface CursoAsignaturaServiceInterface
{
    public function createCursoAsignatura(array $data): CursoAsignatura;
    public function updateCursoAsignatura(CursoAsignatura $cursoAsignatura, array $data): CursoAsignatura;
    public function deleteCursoAsignatura(CursoAsignatura $cursoAsignatura): void;
    public function getCursoAsignaturaById(int $id): ?CursoAsignatura;
    public function getAllCursoAsignaturas(): array;
}
