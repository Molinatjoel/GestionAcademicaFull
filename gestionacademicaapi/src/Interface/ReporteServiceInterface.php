<?php
namespace App\Interface;

use App\Entity\Reporte;

interface ReporteServiceInterface
{
    public function createReporte(array $data): Reporte;
    public function updateReporte(Reporte $reporte, array $data): Reporte;
    public function deleteReporte(Reporte $reporte): void;
    public function getReporteById(int $id): ?Reporte;
    public function getAllReportes(): array;
}
