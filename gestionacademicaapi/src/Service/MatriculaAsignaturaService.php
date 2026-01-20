<?php
namespace App\Service;

use App\Entity\MatriculaAsignatura;
use App\Repository\MatriculaAsignaturaRepository;
use App\Repository\MatriculaRepository;
use App\Repository\CursoAsignaturaRepository;
use Doctrine\ORM\EntityManagerInterface;

class MatriculaAsignaturaService
{
    private MatriculaAsignaturaRepository $repo;
    private EntityManagerInterface $em;
    private MatriculaRepository $matriculaRepo;
    private CursoAsignaturaRepository $cursoAsignaturaRepo;

    public function __construct(
        MatriculaAsignaturaRepository $repo,
        EntityManagerInterface $em,
        MatriculaRepository $matriculaRepo,
        CursoAsignaturaRepository $cursoAsignaturaRepo
    ) {
        $this->repo = $repo;
        $this->em = $em;
        $this->matriculaRepo = $matriculaRepo;
        $this->cursoAsignaturaRepo = $cursoAsignaturaRepo;
    }

    /**
     * Upsert assignments for a matricula given a list of curso_asignatura IDs.
     */
    public function setAsignaturasForMatricula(int $matriculaId, array $cursoAsignaturaIds): array
    {
        $matricula = $this->matriculaRepo->find($matriculaId);
        if (!$matricula) {
            throw new \InvalidArgumentException('Matrícula no encontrada');
        }

        // Existing assignments
        $existing = $this->repo->findByMatriculaId($matriculaId);
        $existingMap = [];
        foreach ($existing as $ea) {
            $existingMap[$ea->getCursoAsignatura()->getIdCursoAsignatura()] = $ea;
        }

        $result = [];
        $desiredSet = array_unique(array_map('intval', $cursoAsignaturaIds));

        // Add new assignments
        foreach ($desiredSet as $caId) {
            if (!isset($existingMap[$caId])) {
                $cursoAsignatura = $this->cursoAsignaturaRepo->find($caId);
                if (!$cursoAsignatura) {
                    // skip invalid id
                    continue;
                }
                $ma = new MatriculaAsignatura();
                $ma->setMatricula($matricula);
                $ma->setCursoAsignatura($cursoAsignatura);
                $ma->setFechaAsignacion(new \DateTime());
                $this->em->persist($ma);
                $existingMap[$caId] = $ma;
            }
        }

        // Remove assignments not desired
        foreach ($existing as $ea) {
            $caId = $ea->getCursoAsignatura()->getIdCursoAsignatura();
            if (!in_array($caId, $desiredSet, true)) {
                $this->em->remove($ea);
                unset($existingMap[$caId]);
            }
        }

        $this->em->flush();

        // Return current set serialized
        foreach ($existingMap as $ma) {
            $result[] = $this->serialize($ma);
        }
        return $result;
    }

    public function listByMatricula(int $matriculaId): array
    {
        return array_map([$this, 'serialize'], $this->repo->findByMatriculaId($matriculaId));
    }

    private function serialize(MatriculaAsignatura $ma): array
    {
        $ca = $ma->getCursoAsignatura();
        $curso = $ca->getCurso();
        $asig = $ca->getAsignatura();
        $doc = $ca->getDocente();
        return [
            'id_matricula_asignatura' => $ma->getIdMatriculaAsignatura(),
            'id_matricula' => $ma->getMatricula()->getIdMatricula(),
            'id_curso_asignatura' => $ca->getIdCursoAsignatura(),
            'curso' => $curso?->getNombreCurso(),
            'asignatura' => $asig?->getNombreAsignatura(),
            'docente' => $doc ? ($doc->getNombres() . ' ' . $doc->getApellidos()) : null,
            'fecha_asignacion' => $ma->getFechaAsignacion()->format('Y-m-d H:i:s'),
        ];
    }
}
