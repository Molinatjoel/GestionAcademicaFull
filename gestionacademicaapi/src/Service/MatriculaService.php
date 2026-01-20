<?php
namespace App\Service;

use App\Entity\Matricula;
use App\Entity\User;
use App\Entity\DatosFamiliares;
use App\Interface\MatriculaServiceInterface;
use App\Repository\MatriculaRepository;
use App\Repository\UserRepository;
use App\Repository\CursoRepository;
use App\Repository\PeriodoLectivoRepository;
use App\Repository\DatosFamiliaresRepository;
use App\Repository\CursoAsignaturaRepository;
use App\Service\MatriculaAsignaturaService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\ChatService;

class MatriculaService implements MatriculaServiceInterface
{
    private MatriculaRepository $matriculaRepository;
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private CursoRepository $cursoRepository;
    private PeriodoLectivoRepository $periodoLectivoRepository;
    private DatosFamiliaresRepository $datosFamiliaresRepository;
    private CursoAsignaturaRepository $cursoAsignaturaRepository;
    private MatriculaAsignaturaService $matriculaAsignaturaService;
    private ChatService $chatService;

    public function __construct(
        MatriculaRepository $matriculaRepository, 
        EntityManagerInterface $em,
        UserRepository $userRepository,
        CursoRepository $cursoRepository,
        PeriodoLectivoRepository $periodoLectivoRepository,
        DatosFamiliaresRepository $datosFamiliaresRepository,
        CursoAsignaturaRepository $cursoAsignaturaRepository,
        MatriculaAsignaturaService $matriculaAsignaturaService,
        ChatService $chatService
    ) {
        $this->matriculaRepository = $matriculaRepository;
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->cursoRepository = $cursoRepository;
        $this->periodoLectivoRepository = $periodoLectivoRepository;
        $this->datosFamiliaresRepository = $datosFamiliaresRepository;
        $this->cursoAsignaturaRepository = $cursoAsignaturaRepository;
        $this->matriculaAsignaturaService = $matriculaAsignaturaService;
        $this->chatService = $chatService;
    }

    public function createMatricula(array $data): Matricula
    {
        $matricula = new Matricula();
        
        // Asignar estudiante
        if (isset($data['id_estudiante'])) {
            $estudiante = $this->userRepository->find($data['id_estudiante']);
            if ($estudiante) {
                $matricula->setEstudiante($estudiante);
            }
        }
        
        // Asignar curso
        if (isset($data['id_curso'])) {
            $curso = $this->cursoRepository->find($data['id_curso']);
            if ($curso) {
                $matricula->setCurso($curso);
            }
        }
        
        // Asignar periodo
        if (isset($data['id_periodo'])) {
            $periodo = $this->periodoLectivoRepository->find($data['id_periodo']);
            if ($periodo) {
                $matricula->setPeriodo($periodo);
            }
        }
        
        // Fecha matrícula
        if (isset($data['fecha_matricula'])) {
            $fecha = is_string($data['fecha_matricula']) 
                ? new \DateTime($data['fecha_matricula']) 
                : $data['fecha_matricula'];
            $matricula->setFechaMatricula($fecha);
        } else {
            $matricula->setFechaMatricula(new \DateTime());
        }
        
        $matricula->setEstado($data['estado'] ?? true);
        
        $this->em->persist($matricula);
        $this->em->flush();

        // Auto-asignar asignaturas del curso a la matrícula
        if ($matricula->getCurso()) {
            $cursoId = $matricula->getCurso()->getIdCurso();
            $cas = $this->cursoAsignaturaRepository->findBy(['curso' => $matricula->getCurso()]);
            $caIds = array_map(static function($ca){ return $ca->getIdCursoAsignatura(); }, $cas);
            if (!empty($caIds)) {
                $this->matriculaAsignaturaService->setAsignaturasForMatricula($matricula->getIdMatricula(), $caIds);
            }

            $this->chatService->ensureGroupChatForCurso($cursoId, $matricula->getEstudiante());
        }
        return $matricula;
    }

    public function updateMatricula(Matricula $matricula, array $data): Matricula
    {
        $oldCurso = $matricula->getCurso();
        $oldEstudiante = $matricula->getEstudiante();

        if (isset($data['id_estudiante'])) {
            $estudiante = $this->userRepository->find($data['id_estudiante']);
            if ($estudiante) {
                $matricula->setEstudiante($estudiante);
            }
        }
        
        if (isset($data['id_curso'])) {
            $curso = $this->cursoRepository->find($data['id_curso']);
            if ($curso) {
                $matricula->setCurso($curso);
            }
        }
        
        if (isset($data['id_periodo'])) {
            $periodo = $this->periodoLectivoRepository->find($data['id_periodo']);
            if ($periodo) {
                $matricula->setPeriodo($periodo);
            }
        }
        
        if (isset($data['fecha_matricula'])) {
            $fecha = is_string($data['fecha_matricula']) 
                ? new \DateTime($data['fecha_matricula']) 
                : $data['fecha_matricula'];
            $matricula->setFechaMatricula($fecha);
        }
        
        if (isset($data['estado'])) {
            $matricula->setEstado($data['estado']);
        }
        
        $this->em->flush();

        // Sync asignaturas si cambió el curso
        if ($matricula->getCurso()) {
            $cas = $this->cursoAsignaturaRepository->findBy(['curso' => $matricula->getCurso()]);
            $caIds = array_map(static function($ca){ return $ca->getIdCursoAsignatura(); }, $cas);
            $this->matriculaAsignaturaService->setAsignaturasForMatricula($matricula->getIdMatricula(), $caIds);

            $this->chatService->ensureGroupChatForCurso($matricula->getCurso()->getIdCurso(), $matricula->getEstudiante());
            $this->chatService->syncCursoChatMembers($matricula->getCurso());
        }

        if ($oldCurso && $oldEstudiante && $matricula->getCurso()) {
            $cursoCambio = $oldCurso->getIdCurso() !== $matricula->getCurso()->getIdCurso();
            $estudianteCambio = $matricula->getEstudiante() && $matricula->getEstudiante()->getId() !== $oldEstudiante->getId();
            if ($cursoCambio || $estudianteCambio) {
                $this->chatService->removeFromCursoChat($oldCurso, $oldEstudiante);
            }
        }
        return $matricula;
    }

    public function deleteMatricula(Matricula $matricula): void
    {
        $curso = $matricula->getCurso();
        $estudiante = $matricula->getEstudiante();
        $this->em->remove($matricula);
        $this->em->flush();

        if ($curso && $estudiante) {
            $this->chatService->removeFromCursoChat($curso, $estudiante);
        }
    }

    public function getMatriculaById(int $id): ?Matricula
    {
        return $this->matriculaRepository->find($id);
    }

    public function getAllMatriculas(): array
    {
        return $this->matriculaRepository->findAll();
    }

    public function getVisibleMatriculasForUser(User $user): array
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return $this->matriculaRepository->findAll();
        }

        $qb = $this->matriculaRepository->createQueryBuilder('m')
            ->leftJoin('m.curso', 'c')
            ->leftJoin('c.docenteTitular', 'd')
            ->addSelect('c', 'd');

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

    public function canViewMatricula(User $user, Matricula $matricula): bool
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return true;
        }

        if (in_array('ROLE_DOCENTE', $roles, true)) {
            return $matricula->getCurso()->getDocenteTitular()->getId() === $user->getId();
        }

        if (in_array('ROLE_ESTUDIANTE', $roles, true)) {
            return $matricula->getEstudiante()->getId() === $user->getId();
        }

        if (in_array('ROLE_PADRE', $roles, true)) {
            $vinculo = $this->datosFamiliaresRepository->findOneBy([
                'representanteUser' => $user,
                'estudiante' => $matricula->getEstudiante(),
            ]);
            return $vinculo !== null;
        }

        return false;
    }

    public function canEditMatricula(User $user, ?Matricula $matricula = null, array $data = []): bool
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return true;
        }

        if (!in_array('ROLE_DOCENTE', $roles, true)) {
            return false;
        }

        if ($matricula && $matricula->getCurso()) {
            $docenteTitular = $matricula->getCurso()->getDocenteTitular();
            return $docenteTitular && $docenteTitular->getId() === $user->getId();
        }

        if (isset($data['id_curso'])) {
            $curso = $this->cursoRepository->find($data['id_curso']);
            $docenteTitular = $curso ? $curso->getDocenteTitular() : null;
            return $docenteTitular && $docenteTitular->getId() === $user->getId();
        }

        return false;
    }
}
