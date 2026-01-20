<?php
namespace App\Service;

use App\Entity\Calificacion;
use App\Entity\User;
use App\Entity\DatosFamiliares;
use App\Interface\CalificacionServiceInterface;
use App\Repository\CalificacionRepository;
use App\Repository\MatriculaRepository;
use App\Repository\CursoAsignaturaRepository;
use App\Repository\DatosFamiliaresRepository;
use Doctrine\ORM\EntityManagerInterface;

class CalificacionService implements CalificacionServiceInterface
{
    private CalificacionRepository $calificacionRepository;
    private EntityManagerInterface $em;
    private MatriculaRepository $matriculaRepository;
    private CursoAsignaturaRepository $cursoAsignaturaRepository;
    private DatosFamiliaresRepository $datosFamiliaresRepository;

    public function __construct(
        CalificacionRepository $calificacionRepository, 
        EntityManagerInterface $em,
        MatriculaRepository $matriculaRepository,
        CursoAsignaturaRepository $cursoAsignaturaRepository,
        DatosFamiliaresRepository $datosFamiliaresRepository
    ) {
        $this->calificacionRepository = $calificacionRepository;
        $this->em = $em;
        $this->matriculaRepository = $matriculaRepository;
        $this->cursoAsignaturaRepository = $cursoAsignaturaRepository;
        $this->datosFamiliaresRepository = $datosFamiliaresRepository;
    }

    public function createCalificacion(array $data): Calificacion
    {
        $calificacion = new Calificacion();
        
        // Asignar matrícula
        if (isset($data['id_matricula'])) {
            $matricula = $this->matriculaRepository->find($data['id_matricula']);
            if ($matricula) {
                $calificacion->setMatricula($matricula);
            }
        }
        
        // Asignar curso-asignatura
        if (isset($data['id_curso_asignatura'])) {
            $cursoAsignatura = $this->cursoAsignaturaRepository->find($data['id_curso_asignatura']);
            if ($cursoAsignatura) {
                $calificacion->setCursoAsignatura($cursoAsignatura);
            }
        }
        
        $calificacion->setNota($data['nota'] ?? 0.0);
        $calificacion->setObservacion($data['observacion'] ?? null);
        $calificacion->setFechaRegistro(new \DateTime());
        
        $this->em->persist($calificacion);
        $this->em->flush();
        return $calificacion;
    }

    public function updateCalificacion(Calificacion $calificacion, array $data): Calificacion
    {
        if (isset($data['id_matricula'])) {
            $matricula = $this->matriculaRepository->find($data['id_matricula']);
            if ($matricula) {
                $calificacion->setMatricula($matricula);
            }
        }
        
        if (isset($data['id_curso_asignatura'])) {
            $cursoAsignatura = $this->cursoAsignaturaRepository->find($data['id_curso_asignatura']);
            if ($cursoAsignatura) {
                $calificacion->setCursoAsignatura($cursoAsignatura);
            }
        }
        
        if (isset($data['nota'])) {
            $calificacion->setNota($data['nota']);
        }
        
        if (isset($data['observacion'])) {
            $calificacion->setObservacion($data['observacion']);
        }
        
        $this->em->flush();
        return $calificacion;
    }

    public function deleteCalificacion(Calificacion $calificacion): void
    {
        $this->em->remove($calificacion);
        $this->em->flush();
    }

    public function getCalificacionById(int $id): ?Calificacion
    {
        return $this->calificacionRepository->find($id);
    }

    public function getAllCalificaciones(): array
    {
        return $this->calificacionRepository->findAll();
    }

    public function getVisibleCalificacionesForUser(User $user): array
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return $this->calificacionRepository->findAll();
        }

        $qb = $this->calificacionRepository->createQueryBuilder('c')
            ->leftJoin('c.matricula', 'm')
            ->leftJoin('c.cursoAsignatura', 'ca')
            ->leftJoin('ca.docente', 'd');

        if (in_array('ROLE_DOCENTE', $roles, true)) {
            $qb->andWhere('d.id = :uid')->setParameter('uid', $user->getId());
            return $qb->getQuery()->getResult();
        }

        if (in_array('ROLE_ESTUDIANTE', $roles, true)) {
            $qb->andWhere('m.estudiante = :uid')->setParameter('uid', $user->getId());
            return $qb->getQuery()->getResult();
        }

        if (in_array('ROLE_PADRE', $roles, true)) {
            $qb->leftJoin(DatosFamiliares::class, 'df', 'WITH', 'df.estudiante = m.estudiante')
               ->andWhere('df.representanteUser = :uid')
               ->setParameter('uid', $user->getId());
            return $qb->getQuery()->getResult();
        }

        return [];
    }

    public function canViewCalificacion(User $user, Calificacion $calificacion): bool
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return true;
        }

        if (in_array('ROLE_DOCENTE', $roles, true)) {
            return $calificacion->getCursoAsignatura()->getDocente()->getId() === $user->getId();
        }

        if (in_array('ROLE_ESTUDIANTE', $roles, true)) {
            return $calificacion->getMatricula()->getEstudiante()->getId() === $user->getId();
        }

        if (in_array('ROLE_PADRE', $roles, true)) {
            $matricula = $calificacion->getMatricula();
            $estudiante = $matricula ? $matricula->getEstudiante() : null;
            if (!$estudiante) {
                return false;
            }
            $vinculo = $this->datosFamiliaresRepository->findOneBy([
                'representanteUser' => $user,
                'estudiante' => $estudiante,
            ]);
            return $vinculo !== null;
        }

        return false;
    }

    public function canEditCalificacion(User $user, ?Calificacion $calificacion = null, array $data = []): bool
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return true;
        }

        if (!in_array('ROLE_DOCENTE', $roles, true)) {
            return false;
        }

        if ($calificacion && $calificacion->getCursoAsignatura()) {
            return $calificacion->getCursoAsignatura()->getDocente()->getId() === $user->getId();
        }

        if (isset($data['id_curso_asignatura'])) {
            $cursoAsignatura = $this->cursoAsignaturaRepository->find($data['id_curso_asignatura']);
            return $cursoAsignatura && $cursoAsignatura->getDocente() && $cursoAsignatura->getDocente()->getId() === $user->getId();
        }

        return false;
    }
}
