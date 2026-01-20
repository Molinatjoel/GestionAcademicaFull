<?php
namespace App\Interface;

use App\Entity\Curso;

interface CursoServiceInterface
{
    public function createCurso(array $data): Curso;
    public function updateCurso(Curso $curso, array $data): Curso;
    public function deleteCurso(Curso $curso): void;
    public function getCursoById(int $id): ?Curso;
    public function getAllCursos(): array;
}
