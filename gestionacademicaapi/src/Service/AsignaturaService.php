<?php
namespace App\Service;

use App\Entity\Asignatura;
use App\Interface\AsignaturaServiceInterface;
use App\Repository\AsignaturaRepository;
use Doctrine\ORM\EntityManagerInterface;

class AsignaturaService implements AsignaturaServiceInterface
{
    private AsignaturaRepository $asignaturaRepository;
    private EntityManagerInterface $em;

    public function __construct(AsignaturaRepository $asignaturaRepository, EntityManagerInterface $em)
    {
        $this->asignaturaRepository = $asignaturaRepository;
        $this->em = $em;
    }

    public function createAsignatura(array $data): Asignatura
    {
        $asignatura = new Asignatura();
        $asignatura->setNombreAsignatura($data['nombre_asignatura'] ?? '');
        $asignatura->setDescripcion($data['descripcion'] ?? null);
        
        $this->em->persist($asignatura);
        $this->em->flush();
        return $asignatura;
    }

    public function updateAsignatura(Asignatura $asignatura, array $data): Asignatura
    {
        if (isset($data['nombre_asignatura'])) {
            $asignatura->setNombreAsignatura($data['nombre_asignatura']);
        }
        if (isset($data['descripcion'])) {
            $asignatura->setDescripcion($data['descripcion']);
        }
        
        $this->em->flush();
        return $asignatura;
    }

    public function deleteAsignatura(Asignatura $asignatura): void
    {
        $this->em->remove($asignatura);
        $this->em->flush();
    }

    public function getAsignaturaById(int $id): ?Asignatura
    {
        return $this->asignaturaRepository->find($id);
    }

    public function getAllAsignaturas(): array
    {
        return $this->asignaturaRepository->findAll();
    }
}
