<?php
namespace App\Service;

use App\Entity\Curso;
use App\Interface\CursoServiceInterface;
use App\Repository\CursoRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\ChatService;

class CursoService implements CursoServiceInterface
{
    private CursoRepository $cursoRepository;
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private ChatService $chatService;

    public function __construct(
        CursoRepository $cursoRepository, 
        EntityManagerInterface $em,
        UserRepository $userRepository,
        ChatService $chatService
    ) {
        $this->cursoRepository = $cursoRepository;
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->chatService = $chatService;
    }

    public function createCurso(array $data): Curso
    {
        $curso = new Curso();
        $curso->setNombreCurso($data['nombre_curso'] ?? '');
        $curso->setNivel($data['nivel'] ?? '');
        
        // Asignar docente titular
        if (isset($data['id_docente_titular'])) {
            $docente = $this->userRepository->find($data['id_docente_titular']);
            if ($docente) {
                $curso->setDocenteTitular($docente);
            }
        }
        
        $curso->setFechaCreacion(new \DateTime());
        $curso->setEstado($data['estado'] ?? true);
        
        $this->em->persist($curso);
        $this->em->flush();

        $this->chatService->ensureGroupChatForCurso($curso->getIdCurso(), $curso->getDocenteTitular(), true);
        return $curso;
    }

    public function updateCurso(Curso $curso, array $data): Curso
    {
        if (isset($data['nombre_curso'])) {
            $curso->setNombreCurso($data['nombre_curso']);
        }
        if (isset($data['nivel'])) {
            $curso->setNivel($data['nivel']);
        }
        if (isset($data['id_docente_titular'])) {
            $docente = $this->userRepository->find($data['id_docente_titular']);
            if ($docente) {
                $curso->setDocenteTitular($docente);
            }
        }
        if (isset($data['estado'])) {
            $curso->setEstado($data['estado']);
        }
        
        $this->em->flush();
        return $curso;
    }

    public function deleteCurso(Curso $curso): void
    {
        $this->em->remove($curso);
        $this->em->flush();
    }

    public function getCursoById(int $id): ?Curso
    {
        return $this->cursoRepository->find($id);
    }

    public function getAllCursos(): array
    {
        return $this->cursoRepository->findAll();
    }
}
