<?php
namespace App\Interface;

use App\Entity\User;

interface UserServiceInterface
{
    public function createUser(array $data): User;
    public function updateUser(User $user, array $data): User;
    public function deleteUser(User $user): void;
    public function getUserById(int $id): ?User;
    public function getAllUsers(): array;
}
