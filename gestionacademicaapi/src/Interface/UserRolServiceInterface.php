<?php
namespace App\Interface;

use App\Entity\UserRol;

interface UserRolServiceInterface
{
    public function createUserRol(array $data): UserRol;
    public function updateUserRol(UserRol $userRol, array $data): UserRol;
    public function deleteUserRol(UserRol $userRol): void;
    public function getUserRolById(int $id): ?UserRol;
    public function getAllUserRoles(): array;
}
