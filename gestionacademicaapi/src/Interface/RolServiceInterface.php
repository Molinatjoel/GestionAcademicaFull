<?php
namespace App\Interface;

use App\Entity\Rol;

interface RolServiceInterface
{
    public function createRol(array $data): Rol;
    public function updateRol(Rol $rol, array $data): Rol;
    public function deleteRol(Rol $rol): void;
    public function getRolById(int $id): ?Rol;
    public function getAllRoles(): array;
}
