<?php
namespace App\Service;

use App\Entity\Rol;
use App\Interface\RolServiceInterface;
use App\Repository\RolRepository;
use Doctrine\ORM\EntityManagerInterface;

class RolService implements RolServiceInterface
{
    private RolRepository $rolRepository;
    private EntityManagerInterface $em;

    public function __construct(RolRepository $rolRepository, EntityManagerInterface $em)
    {
        $this->rolRepository = $rolRepository;
        $this->em = $em;
    }

    public function createRol(array $data): Rol
    {
        $rol = new Rol();
        $rol->setNombreRol($data['nombre_rol'] ?? '');
        $rol->setDescripcion($data['descripcion'] ?? null);
        
        $this->em->persist($rol);
        $this->em->flush();
        return $rol;
    }

    public function updateRol(Rol $rol, array $data): Rol
    {
        if (isset($data['nombre_rol'])) {
            $rol->setNombreRol($data['nombre_rol']);
        }
        if (isset($data['descripcion'])) {
            $rol->setDescripcion($data['descripcion']);
        }
        
        $this->em->flush();
        return $rol;
    }

    public function deleteRol(Rol $rol): void
    {
        $this->em->remove($rol);
        $this->em->flush();
    }

    public function getRolById(int $id): ?Rol
    {
        return $this->rolRepository->find($id);
    }

    public function getAllRoles(): array
    {
        return $this->rolRepository->findAll();
    }
}
