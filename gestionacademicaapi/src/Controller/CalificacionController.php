<?php
namespace App\Controller;

use App\Service\CalificacionService;
use App\Request\CalificacionRequest;
use App\Entity\Calificacion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CalificacionController extends AbstractController
{
    private CalificacionService $calificacionService;
    private ValidatorInterface $validator;
    private Security $security;

    // Inyección del servicio CalificacionService y el validador
    public function __construct(CalificacionService $calificacionService, ValidatorInterface $validator, Security $security)
    {
        $this->calificacionService = $calificacionService;
        $this->validator = $validator;
        $this->security = $security;
    }

    // Crear calificación
    #[Route('/api/calificaciones', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (!$this->calificacionService->canEditCalificacion($user, null, $data)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }

        $calificacionRequest = new CalificacionRequest();
        $calificacionRequest->id_matricula = $data['id_matricula'] ?? null;
        $calificacionRequest->id_curso_asignatura = $data['id_curso_asignatura'] ?? null;
        $calificacionRequest->nota = $data['nota'] ?? null;
        $calificacionRequest->observacion = $data['observacion'] ?? null;
        $calificacionRequest->fecha_registro = new \DateTime();
        $errors = $this->validator->validate($calificacionRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            //Errores de validación
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $calificacion = $this->calificacionService->createCalificacion($data);
        $matricula = $calificacion->getMatricula();
        $estudiante = $matricula?->getEstudiante();
        $cursoAsignatura = $calificacion->getCursoAsignatura();
        $curso = $cursoAsignatura?->getCurso();
        $asignatura = $cursoAsignatura?->getAsignatura();
        $docente = $cursoAsignatura?->getDocente();
        return $this->json([
            'id_calificacion' => $calificacion->getIdCalificacion(),
            'id_matricula' => $matricula?->getIdMatricula(),
            'id_curso_asignatura' => $cursoAsignatura?->getIdCursoAsignatura(),
            'estudiante' => $estudiante ? trim(($estudiante->getNombres() ?? '') . ' ' . ($estudiante->getApellidos() ?? '')) : null,
            'curso' => $curso?->getNombreCurso(),
            'asignatura' => $asignatura?->getNombreAsignatura(),
            'docente' => $docente ? trim(($docente->getNombres() ?? '') . ' ' . ($docente->getApellidos() ?? '')) : null,
            'nota' => $calificacion->getNota(),
            'observacion' => $calificacion->getObservacion(),
            'fecha_registro' => $calificacion->getFechaRegistro()?->format('Y-m-d'),
        ]);
    }

    // Listar todas las calificaciones
    #[Route('/api/calificaciones', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        $calificaciones = $this->calificacionService->getVisibleCalificacionesForUser($user);
        $payload = array_map(static function (Calificacion $c) {
            $matricula = $c->getMatricula();
            $estudiante = $matricula?->getEstudiante();
            $cursoAsignatura = $c->getCursoAsignatura();
            $curso = $cursoAsignatura?->getCurso();
            $asignatura = $cursoAsignatura?->getAsignatura();
            $docente = $cursoAsignatura?->getDocente();

            return [
                'id_calificacion' => $c->getIdCalificacion(),
                'id_matricula' => $matricula?->getIdMatricula(),
                'id_curso_asignatura' => $cursoAsignatura?->getIdCursoAsignatura(),
                'estudiante' => $estudiante ? trim(($estudiante->getNombres() ?? '') . ' ' . ($estudiante->getApellidos() ?? '')) : null,
                'curso' => $curso?->getNombreCurso(),
                'asignatura' => $asignatura?->getNombreAsignatura(),
                'docente' => $docente ? trim(($docente->getNombres() ?? '') . ' ' . ($docente->getApellidos() ?? '')) : null,
                'nota' => $c->getNota(),
                'observacion' => $c->getObservacion(),
                'fecha_registro' => $c->getFechaRegistro()?->format('Y-m-d'),
            ];
        }, $calificaciones);

        return $this->json($payload);
    }

    // Obtener calificación por ID
    #[Route('/api/calificaciones/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $calificacion = $this->calificacionService->getCalificacionById($id);
        if (!$calificacion) {
            //Calificación no encontrada
            return $this->json(['error' => 'Calificación no encontrada'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user || !$this->calificacionService->canViewCalificacion($user, $calificacion)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        $matricula = $calificacion->getMatricula();
        $estudiante = $matricula?->getEstudiante();
        $cursoAsignatura = $calificacion->getCursoAsignatura();
        $curso = $cursoAsignatura?->getCurso();
        $asignatura = $cursoAsignatura?->getAsignatura();
        return $this->json([
            'id_calificacion' => $calificacion->getIdCalificacion(),
            'id_matricula' => $matricula?->getIdMatricula(),
            'id_curso_asignatura' => $cursoAsignatura?->getIdCursoAsignatura(),
            'estudiante' => $estudiante ? trim(($estudiante->getNombres() ?? '') . ' ' . ($estudiante->getApellidos() ?? '')) : null,
            'curso' => $curso?->getNombreCurso(),
            'asignatura' => $asignatura?->getNombreAsignatura(),
            'nota' => $calificacion->getNota(),
            'observacion' => $calificacion->getObservacion(),
            'fecha_registro' => $calificacion->getFechaRegistro()?->format('Y-m-d'),
        ]);
    }

    // Actualizar calificación
    #[Route('/api/calificaciones/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $calificacion = $this->calificacionService->getCalificacionById($id);
        if (!$calificacion) {
            //Calificación no encontrada
            return $this->json(['error' => 'Calificación no encontrada'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        if (!$this->calificacionService->canEditCalificacion($user, $calificacion)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        $data = json_decode($request->getContent(), true);
        $calificacion = $this->calificacionService->updateCalificacion($calificacion, $data);
        $matricula = $calificacion->getMatricula();
        $estudiante = $matricula?->getEstudiante();
        $cursoAsignatura = $calificacion->getCursoAsignatura();
        $curso = $cursoAsignatura?->getCurso();
        $asignatura = $cursoAsignatura?->getAsignatura();
        return $this->json([
            'id_calificacion' => $calificacion->getIdCalificacion(),
            'id_matricula' => $matricula?->getIdMatricula(),
            'id_curso_asignatura' => $cursoAsignatura?->getIdCursoAsignatura(),
            'estudiante' => $estudiante ? trim(($estudiante->getNombres() ?? '') . ' ' . ($estudiante->getApellidos() ?? '')) : null,
            'curso' => $curso?->getNombreCurso(),
            'asignatura' => $asignatura?->getNombreAsignatura(),
            'nota' => $calificacion->getNota(),
            'observacion' => $calificacion->getObservacion(),
            'fecha_registro' => $calificacion->getFechaRegistro()?->format('Y-m-d'),
        ]);
    }

    // Eliminar calificación
    #[Route('/api/calificaciones/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $calificacion = $this->calificacionService->getCalificacionById($id);
        if (!$calificacion) {
            //Calificación no encontrada
            return $this->json(['error' => 'Calificación no encontrada'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        if (!$this->calificacionService->canEditCalificacion($user, $calificacion)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        $this->calificacionService->deleteCalificacion($calificacion);
        //Calificación eliminada correctamente
        return $this->json(['message' => 'Calificación eliminada correctamente']);
    }
}
