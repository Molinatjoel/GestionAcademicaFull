<?php
namespace App\Service;

use App\Entity\PeriodoLectivo;
use App\Interface\PeriodoLectivoServiceInterface;
use App\Repository\PeriodoLectivoRepository;
use Doctrine\ORM\EntityManagerInterface;

class PeriodoLectivoService implements PeriodoLectivoServiceInterface
{
    private PeriodoLectivoRepository $periodoLectivoRepository;
    private EntityManagerInterface $em;

    public function __construct(PeriodoLectivoRepository $periodoLectivoRepository, EntityManagerInterface $em)
    {
        $this->periodoLectivoRepository = $periodoLectivoRepository;
        $this->em = $em;
    }

    public function createPeriodoLectivo(array $data): PeriodoLectivo
    {
        $periodoLectivo = new PeriodoLectivo();
        $periodoLectivo->setDescripcion($data['nombre'] ?? $data['descripcion'] ?? '');
        
        // Fecha inicio
        if (isset($data['fecha_inicio'])) {
            $fechaInicio = is_string($data['fecha_inicio']) 
                ? new \DateTime($data['fecha_inicio']) 
                : $data['fecha_inicio'];
            $periodoLectivo->setFechaInicio($fechaInicio);
        }
        
        // Fecha fin
        if (isset($data['fecha_fin'])) {
            $fechaFin = is_string($data['fecha_fin']) 
                ? new \DateTime($data['fecha_fin']) 
                : $data['fecha_fin'];
            $periodoLectivo->setFechaFin($fechaFin);
        }
        
        $periodoLectivo->setEstado($data['estado'] ?? true);
        
        $this->em->persist($periodoLectivo);
        $this->em->flush();
        return $periodoLectivo;
    }

    public function updatePeriodoLectivo(PeriodoLectivo $periodoLectivo, array $data): PeriodoLectivo
    {
        if (isset($data['nombre'])) {
            $periodoLectivo->setDescripcion($data['nombre']);
        } elseif (isset($data['descripcion'])) {
            $periodoLectivo->setDescripcion($data['descripcion']);
        }
        
        if (isset($data['fecha_inicio'])) {
            $fechaInicio = is_string($data['fecha_inicio']) 
                ? new \DateTime($data['fecha_inicio']) 
                : $data['fecha_inicio'];
            $periodoLectivo->setFechaInicio($fechaInicio);
        }
        
        if (isset($data['fecha_fin'])) {
            $fechaFin = is_string($data['fecha_fin']) 
                ? new \DateTime($data['fecha_fin']) 
                : $data['fecha_fin'];
            $periodoLectivo->setFechaFin($fechaFin);
        }
        
        if (isset($data['estado'])) {
            $periodoLectivo->setEstado($data['estado']);
        }
        
        $this->em->flush();
        return $periodoLectivo;
    }

    public function deletePeriodoLectivo(PeriodoLectivo $periodoLectivo): void
    {
        $this->em->remove($periodoLectivo);
        $this->em->flush();
    }

    public function getPeriodoLectivoById(int $id): ?PeriodoLectivo
    {
        return $this->periodoLectivoRepository->find($id);
    }

    public function getAllPeriodoLectivo(): array
    {
        return $this->periodoLectivoRepository->findAll();
    }
}
