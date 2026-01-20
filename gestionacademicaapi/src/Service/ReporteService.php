<?php
namespace App\Service;

use App\Entity\Reporte;
use App\Interface\ReporteServiceInterface;
use App\Repository\ReporteRepository;
use App\Repository\CursoRepository;
use App\Repository\UserRepository;
use App\Repository\PeriodoLectivoRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReporteService implements ReporteServiceInterface
{
    private ReporteRepository $reporteRepository;
    private EntityManagerInterface $em;
    private CursoRepository $cursoRepository;
    private UserRepository $userRepository;
    private PeriodoLectivoRepository $periodoLectivoRepository;

    public function __construct(
        ReporteRepository $reporteRepository, 
        EntityManagerInterface $em,
        CursoRepository $cursoRepository,
        UserRepository $userRepository,
        PeriodoLectivoRepository $periodoLectivoRepository
    ) {
        $this->reporteRepository = $reporteRepository;
        $this->em = $em;
        $this->cursoRepository = $cursoRepository;
        $this->userRepository = $userRepository;
        $this->periodoLectivoRepository = $periodoLectivoRepository;
    }

    public function createReporte(array $data): Reporte
    {
        $reporte = new Reporte();
        $reporte->setTitulo($data['titulo'] ?? '');
        $reporte->setDescripcion($data['descripcion'] ?? '');
        $reporte->setTipo($data['tipo'] ?? '');
        
        // Asignar curso
        if (isset($data['id_curso'])) {
            $curso = $this->cursoRepository->find($data['id_curso']);
            if ($curso) {
                $reporte->setCurso($curso);
            }
        }
        
        // Asignar docente
        if (isset($data['id_docente'])) {
            $docente = $this->userRepository->find($data['id_docente']);
            if ($docente) {
                $reporte->setDocente($docente);
            }
        }
        
        // Asignar periodo
        if (isset($data['id_periodo'])) {
            $periodo = $this->periodoLectivoRepository->find($data['id_periodo']);
            if ($periodo) {
                $reporte->setPeriodo($periodo);
            }
        }
        
        $reporte->setFechaCreacion(new \DateTime());
        
        $this->em->persist($reporte);
        $this->em->flush();
        return $reporte;
    }

    public function updateReporte(Reporte $reporte, array $data): Reporte
    {
        if (isset($data['titulo'])) {
            $reporte->setTitulo($data['titulo']);
        }
        if (isset($data['descripcion'])) {
            $reporte->setDescripcion($data['descripcion']);
        }
        if (isset($data['tipo'])) {
            $reporte->setTipo($data['tipo']);
        }
        
        if (isset($data['id_curso'])) {
            $curso = $this->cursoRepository->find($data['id_curso']);
            if ($curso) {
                $reporte->setCurso($curso);
            }
        }
        
        if (isset($data['id_docente'])) {
            $docente = $this->userRepository->find($data['id_docente']);
            if ($docente) {
                $reporte->setDocente($docente);
            }
        }
        
        if (isset($data['id_periodo'])) {
            $periodo = $this->periodoLectivoRepository->find($data['id_periodo']);
            if ($periodo) {
                $reporte->setPeriodo($periodo);
            }
        }
        
        $this->em->flush();
        return $reporte;
    }

    public function deleteReporte(Reporte $reporte): void
    {
        $this->em->remove($reporte);
        $this->em->flush();
    }

    public function getReporteById(int $id): ?Reporte
    {
        return $this->reporteRepository->find($id);
    }

    public function getAllReportes(): array
    {
        return $this->reporteRepository->findAll();
    }
}
