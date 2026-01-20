<?php
namespace App\Service;

use App\Entity\UserRol;
use App\Interface\UserRolServiceInterface;
use App\Repository\UserRolRepository;
use App\Repository\UserRepository;
use App\Repository\RolRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserRolService implements UserRolServiceInterface
{
    private UserRolRepository $userRolRepository;
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private RolRepository $rolRepository;

    public function __construct(
        UserRolRepository $userRolRepository, 
        EntityManagerInterface $em,
        UserRepository $userRepository,
        RolRepository $rolRepository
    ) {
        $this->userRolRepository = $userRolRepository;
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->rolRepository = $rolRepository;
    }

    public function createUserRol(array $data): UserRol
    {
        $userRol = new UserRol();
        
        // Buscar usuario
        if (isset($data['id_user'])) {
            $user = $this->userRepository->find($data['id_user']);
            if ($user) {
                $userRol->setUser($user);
            }
        }
        
        // Buscar rol
        if (isset($data['id_rol'])) {
            $rol = $this->rolRepository->find($data['id_rol']);
            if ($rol) {
                $userRol->setRol($rol);
            }
        }
        
        $this->em->persist($userRol);
        $this->em->flush();
        return $userRol;
    }

    public function updateUserRol(UserRol $userRol, array $data): UserRol
    {
        // Actualizar usuario
        if (isset($data['id_user'])) {
            $user = $this->userRepository->find($data['id_user']);
            if ($user) {
                $userRol->setUser($user);
            }
        }
        
        // Actualizar rol
        if (isset($data['id_rol'])) {
            $rol = $this->rolRepository->find($data['id_rol']);
            if ($rol) {
                $userRol->setRol($rol);
            }
        }
        
        $this->em->flush();
        return $userRol;
    }

    public function deleteUserRol(UserRol $userRol): void
    {
        $this->em->remove($userRol);
        $this->em->flush();
    }

    public function getUserRolById(int $id): ?UserRol
    {
        return $this->userRolRepository->find($id);
    }

    public function getAllUserRoles(): array
    {
        return $this->userRolRepository->findAll();
    }
}
