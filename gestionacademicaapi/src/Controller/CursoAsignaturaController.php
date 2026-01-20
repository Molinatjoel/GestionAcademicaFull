<?php
namespace App\Controller;

use App\Service\CursoAsignaturaService;
use App\Request\CursoAsignaturaRequest;
use App\Entity\CursoAsignatura;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CursoAsignaturaController extends AbstractController
{
    private CursoAsignaturaService $cursoAsignaturaService;
    private ValidatorInterface $validator;

    // Inyección del servicio CursoAsignaturaService y el validador
    public function __construct(CursoAsignaturaService $cursoAsignaturaService, ValidatorInterface $validator)
    {
        $this->cursoAsignaturaService = $cursoAsignaturaService;
        $this->validator = $validator;
    }

    // Crear relación curso-asignatura
    #[Route('/api/curso-asignaturas', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $cursoAsignaturaRequest = new CursoAsignaturaRequest();
        $cursoAsignaturaRequest->id_curso = $data['id_curso'] ?? null;
        $cursoAsignaturaRequest->id_asignatura = $data['id_asignatura'] ?? null;
        $cursoAsignaturaRequest->id_docente = $data['id_docente'] ?? null;
        $cursoAsignaturaRequest->fecha_creacion = new \DateTime();
        $errors = $this->validator->validate($cursoAsignaturaRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            //Errores de validación
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $cursoAsignatura = $this->cursoAsignaturaService->createCursoAsignatura($data);
        $curso = $cursoAsignatura->getCurso();
        $asignatura = $cursoAsignatura->getAsignatura();
        $docente = $cursoAsignatura->getDocente();
        return $this->json([
            'id_curso_asignatura' => $cursoAsignatura->getIdCursoAsignatura(),
            'id_curso' => $curso?->getIdCurso(),
            'curso' => $curso?->getNombreCurso(),
            'id_asignatura' => $asignatura?->getIdAsignatura(),
            'asignatura' => $asignatura?->getNombreAsignatura(),
            'id_docente' => $docente?->getId(),
            'docente' => $docente ? trim(($docente->getNombres() ?? '') . ' ' . ($docente->getApellidos() ?? '')) : null,
        ]);
    }

    // Listar todas las relaciones curso-asignatura
    #[Route('/api/curso-asignaturas', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $cursoAsignaturas = $this->cursoAsignaturaService->getAllCursoAsignaturas();
        $payload = array_map(static function (CursoAsignatura $ca) {
            $curso = $ca->getCurso();
            $asignatura = $ca->getAsignatura();
            $docente = $ca->getDocente();

            return [
                'id_curso_asignatura' => $ca->getIdCursoAsignatura(),
                'id_curso' => $curso?->getIdCurso(),
                'curso' => $curso?->getNombreCurso(),
                'id_asignatura' => $asignatura?->getIdAsignatura(),
                'asignatura' => $asignatura?->getNombreAsignatura(),
                'id_docente' => $docente?->getId(),
                'docente' => $docente ? trim(($docente->getNombres() ?? '') . ' ' . ($docente->getApellidos() ?? '')) : null,
            ];
        }, $cursoAsignaturas);

        return $this->json($payload);
    }

    // Obtener relación por ID
    #[Route('/api/curso-asignaturas/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $cursoAsignatura = $this->cursoAsignaturaService->getCursoAsignaturaById($id);
        if (!$cursoAsignatura) {
            //Relación no encontrada
            return $this->json(['error' => 'Relación no encontrada'], Response::HTTP_NOT_FOUND);
        }
        $curso = $cursoAsignatura->getCurso();
        $asignatura = $cursoAsignatura->getAsignatura();
        $docente = $cursoAsignatura->getDocente();
        return $this->json([
            'id_curso_asignatura' => $cursoAsignatura->getIdCursoAsignatura(),
            'id_curso' => $curso?->getIdCurso(),
            'curso' => $curso?->getNombreCurso(),
            'id_asignatura' => $asignatura?->getIdAsignatura(),
            'asignatura' => $asignatura?->getNombreAsignatura(),
            'id_docente' => $docente?->getId(),
            'docente' => $docente ? trim(($docente->getNombres() ?? '') . ' ' . ($docente->getApellidos() ?? '')) : null,
        ]);
    }

    // Actualizar relación
    #[Route('/api/curso-asignaturas/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $cursoAsignatura = $this->cursoAsignaturaService->getCursoAsignaturaById($id);
        if (!$cursoAsignatura) {
            //Relación no encontrada
            return $this->json(['error' => 'Relación no encontrada'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        $cursoAsignatura = $this->cursoAsignaturaService->updateCursoAsignatura($cursoAsignatura, $data);
        $curso = $cursoAsignatura->getCurso();
        $asignatura = $cursoAsignatura->getAsignatura();
        $docente = $cursoAsignatura->getDocente();
        return $this->json([
            'id_curso_asignatura' => $cursoAsignatura->getIdCursoAsignatura(),
            'id_curso' => $curso?->getIdCurso(),
            'curso' => $curso?->getNombreCurso(),
            'id_asignatura' => $asignatura?->getIdAsignatura(),
            'asignatura' => $asignatura?->getNombreAsignatura(),
            'id_docente' => $docente?->getId(),
            'docente' => $docente ? trim(($docente->getNombres() ?? '') . ' ' . ($docente->getApellidos() ?? '')) : null,
        ]);
    }

    // Eliminar relación
    #[Route('/api/curso-asignaturas/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $cursoAsignatura = $this->cursoAsignaturaService->getCursoAsignaturaById($id);
        if (!$cursoAsignatura) {
            //Relación no encontrada
            return $this->json(['error' => 'Relación no encontrada'], Response::HTTP_NOT_FOUND);
        }
        $this->cursoAsignaturaService->deleteCursoAsignatura($cursoAsignatura);
        //Relación eliminada correctamente
        return $this->json(['message' => 'Relación eliminada correctamente']);
    }

    // Bulk: asignaturas por curso
    #[Route('/api/cursos/{cursoId}/asignaturas-bulk', methods: ['POST'])]
    public function bulkCursoAsignaturas(Request $request, int $cursoId): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $ids = is_array($data['asignatura_ids'] ?? null) ? $data['asignatura_ids'] : [];
        try {
            $result = $this->cursoAsignaturaService->setAsignaturasForCurso($cursoId, $ids);
            return $this->json($result);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
