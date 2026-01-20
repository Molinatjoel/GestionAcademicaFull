<?php
namespace App\Service;

use App\Entity\CursoAsignatura;
use App\Interface\CursoAsignaturaServiceInterface;
use App\Repository\CursoAsignaturaRepository;
use App\Repository\CursoRepository;
use App\Repository\AsignaturaRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class CursoAsignaturaService implements CursoAsignaturaServiceInterface
{
    private CursoAsignaturaRepository $cursoAsignaturaRepository;
    private EntityManagerInterface $em;
    private CursoRepository $cursoRepository;
    private AsignaturaRepository $asignaturaRepository;
    private UserRepository $userRepository;

    public function __construct(
        CursoAsignaturaRepository $cursoAsignaturaRepository, 
        EntityManagerInterface $em,
        CursoRepository $cursoRepository,
        AsignaturaRepository $asignaturaRepository,
        UserRepository $userRepository
    ) {
        $this->cursoAsignaturaRepository = $cursoAsignaturaRepository;
        $this->em = $em;
        $this->cursoRepository = $cursoRepository;
        $this->asignaturaRepository = $asignaturaRepository;
        $this->userRepository = $userRepository;
    }

    public function createCursoAsignatura(array $data): CursoAsignatura
    {
        $cursoAsignatura = new CursoAsignatura();
        
        // Asignar curso
        if (isset($data['id_curso'])) {
            $curso = $this->cursoRepository->find($data['id_curso']);
            if ($curso) {
                $cursoAsignatura->setCurso($curso);
            }
        }
        
        // Asignar asignatura
        if (isset($data['id_asignatura'])) {
            $asignatura = $this->asignaturaRepository->find($data['id_asignatura']);
            if ($asignatura) {
                $cursoAsignatura->setAsignatura($asignatura);
            }
        }
        
        // Asignar docente
        if (isset($data['id_docente'])) {
            $docente = $this->userRepository->find($data['id_docente']);
            if ($docente) {
                $cursoAsignatura->setDocente($docente);
            }
        }
        
        $cursoAsignatura->setFechaCreacion(new \DateTime());
        
        $this->em->persist($cursoAsignatura);
        $this->em->flush();
        return $cursoAsignatura;
    }

    public function updateCursoAsignatura(CursoAsignatura $cursoAsignatura, array $data): CursoAsignatura
    {
        if (isset($data['id_curso'])) {
            $curso = $this->cursoRepository->find($data['id_curso']);
            if ($curso) {
                $cursoAsignatura->setCurso($curso);
            }
        }
        
        if (isset($data['id_asignatura'])) {
            $asignatura = $this->asignaturaRepository->find($data['id_asignatura']);
            if ($asignatura) {
                $cursoAsignatura->setAsignatura($asignatura);
            }
        }
        
        if (isset($data['id_docente'])) {
            $docente = $this->userRepository->find($data['id_docente']);
            if ($docente) {
                $cursoAsignatura->setDocente($docente);
            }
        }
        
        $this->em->flush();
        return $cursoAsignatura;
    }

    public function deleteCursoAsignatura(CursoAsignatura $cursoAsignatura): void
    {
        $this->em->remove($cursoAsignatura);
        $this->em->flush();
    }

    public function getCursoAsignaturaById(int $id): ?CursoAsignatura
    {
        return $this->cursoAsignaturaRepository->find($id);
    }

    public function getAllCursoAsignaturas(): array
    {
        return $this->cursoAsignaturaRepository->findAll();
    }

    /**
     * Set subjects for a course (bulk upsert). Removes relations not present.
     */
    public function setAsignaturasForCurso(int $cursoId, array $asignaturaIds): array
    {
        $curso = $this->cursoRepository->find($cursoId);
        if (!$curso) {
            throw new \InvalidArgumentException('Curso no encontrado');
        }

        $desired = array_unique(array_map('intval', $asignaturaIds));
        $existing = $this->cursoAsignaturaRepository->findBy(['curso' => $curso]);
        $existingMap = [];
        foreach ($existing as $ca) {
            $existingMap[$ca->getAsignatura()->getIdAsignatura()] = $ca;
        }

        $result = [];
        // Add missing
        foreach ($desired as $asid) {
            if (!isset($existingMap[$asid])) {
                $asig = $this->asignaturaRepository->find($asid);
                if (!$asig) { continue; }
                $ca = new CursoAsignatura();
                $ca->setCurso($curso);
                $ca->setAsignatura($asig);
                // Docente opcional (null)
                $ca->setFechaCreacion(new \DateTime());
                $this->em->persist($ca);
                $existingMap[$asid] = $ca;
            }
        }

        // Remove not desired
        foreach ($existing as $ca) {
            $asid = $ca->getAsignatura()->getIdAsignatura();
            if (!in_array($asid, $desired, true)) {
                $this->em->remove($ca);
                unset($existingMap[$asid]);
            }
        }

        $this->em->flush();

        foreach ($existingMap as $ca) {
            $result[] = [
                'id_curso_asignatura' => $ca->getIdCursoAsignatura(),
                'id_curso' => $curso->getIdCurso(),
                'id_asignatura' => $ca->getAsignatura()->getIdAsignatura(),
            ];
        }
        return $result;
    }
}
