<?php
namespace App\Interface;

use App\Entity\PeriodoLectivo;

interface PeriodoLectivoServiceInterface
{
    public function createPeriodoLectivo(array $data): PeriodoLectivo;
    public function updatePeriodoLectivo(PeriodoLectivo $periodoLectivo, array $data): PeriodoLectivo;
    public function deletePeriodoLectivo(PeriodoLectivo $periodoLectivo): void;
    public function getPeriodoLectivoById(int $id): ?PeriodoLectivo;
    public function getAllPeriodoLectivo(): array;
}
