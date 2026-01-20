<?php
namespace App\Interface;

use App\Entity\DatosFamiliares;

interface DatosFamiliaresServiceInterface
{
    public function createDatosFamiliares(array $data): DatosFamiliares;
    public function updateDatosFamiliares(DatosFamiliares $datosFamiliares, array $data): DatosFamiliares;
    public function deleteDatosFamiliares(DatosFamiliares $datosFamiliares): void;
    public function getDatosFamiliaresById(int $id): ?DatosFamiliares;
    public function getAllDatosFamiliares(): array;
}
