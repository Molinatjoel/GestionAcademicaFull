<?php
namespace App\Controller;

use App\Service\MatriculaService;
use App\Request\MatriculaRequest;
use App\Entity\Matricula;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MatriculaController extends AbstractController
{
    private MatriculaService $matriculaService;
    private ValidatorInterface $validator;
    private Security $security;

    // Inyección del servicio MatriculaService y el validador
    public function __construct(MatriculaService $matriculaService, ValidatorInterface $validator, Security $security)
    {
        $this->matriculaService = $matriculaService;
        $this->validator = $validator;
        $this->security = $security;
    }

    // Crear matrícula
    #[Route('/api/matriculas', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (!$this->matriculaService->canEditMatricula($user, null, $data)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }

        $matriculaRequest = new MatriculaRequest();
        $matriculaRequest->id_estudiante = $data['id_estudiante'] ?? null;
        $matriculaRequest->id_curso = $data['id_curso'] ?? null;
        $matriculaRequest->id_periodo = $data['id_periodo'] ?? null;
        $matriculaRequest->fecha_matricula = isset($data['fecha_matricula']) ? new \DateTime($data['fecha_matricula']) : null;
        $matriculaRequest->estado = $data['estado'] ?? true;
        $errors = $this->validator->validate($matriculaRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            //Errores de validación
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $matricula = $this->matriculaService->createMatricula($data);
        $estudiante = $matricula->getEstudiante();
        $curso = $matricula->getCurso();
        $periodo = $matricula->getPeriodo();
        return $this->json([
            'id_matricula' => $matricula->getIdMatricula(),
            'id_estudiante' => $estudiante?->getId(),
            'id_curso' => $curso?->getIdCurso(),
            'id_periodo' => $periodo?->getIdPeriodo(),
            'estado' => $matricula->isEstado(),
            'fecha_matricula' => $matricula->getFechaMatricula()?->format('Y-m-d'),
            'estudiante' => $estudiante ? ($estudiante->getNombres() . ' ' . $estudiante->getApellidos()) : null,
            'curso' => $curso ? $curso->getNombreCurso() : null,
            'periodo' => $periodo ? $periodo->getDescripcion() : null,
        ]);
    }

    // Listar todas las matrículas
    #[Route('/api/matriculas', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        $matriculas = $this->matriculaService->getVisibleMatriculasForUser($user);

        // Serializamos a datos planos para el frontend (evita objetos anidados en el JSON)
        $payload = array_map(static function (Matricula $m) {
            $estudiante = $m->getEstudiante();
            $curso = $m->getCurso();
            $periodo = $m->getPeriodo();

            return [
                'id_matricula' => $m->getIdMatricula(),
                'id_estudiante' => $estudiante?->getId(),
                'id_curso' => $curso?->getIdCurso(),
                'id_periodo' => $periodo?->getIdPeriodo(),
                'estado' => $m->isEstado(),
                'fecha_matricula' => $m->getFechaMatricula()?->format('Y-m-d'),
                'estudiante' => $estudiante ? ($estudiante->getNombres() . ' ' . $estudiante->getApellidos()) : null,
                'curso' => $curso ? $curso->getNombreCurso() : null,
                'periodo' => $periodo ? $periodo->getDescripcion() : null,
            ];
        }, $matriculas);

        return $this->json($payload);
    }

    // Obtener matrícula por ID
    #[Route('/api/matriculas/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $matricula = $this->matriculaService->getMatriculaById($id);
        if (!$matricula) {
            //Matrícula no encontrada
            return $this->json(['error' => 'Matrícula no encontrada'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user || !$this->matriculaService->canViewMatricula($user, $matricula)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        $estudiante = $matricula->getEstudiante();
        $curso = $matricula->getCurso();
        $periodo = $matricula->getPeriodo();
        return $this->json([
            'id_matricula' => $matricula->getIdMatricula(),
            'id_estudiante' => $estudiante?->getId(),
            'id_curso' => $curso?->getIdCurso(),
            'id_periodo' => $periodo?->getIdPeriodo(),
            'estado' => $matricula->isEstado(),
            'fecha_matricula' => $matricula->getFechaMatricula()?->format('Y-m-d'),
            'estudiante' => $estudiante ? ($estudiante->getNombres() . ' ' . $estudiante->getApellidos()) : null,
            'curso' => $curso ? $curso->getNombreCurso() : null,
            'periodo' => $periodo ? $periodo->getDescripcion() : null,
        ]);
    }

    // Actualizar matrícula
    #[Route('/api/matriculas/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $matricula = $this->matriculaService->getMatriculaById($id);
        if (!$matricula) {
            //Matrícula no encontrada
            return $this->json(['error' => 'Matrícula no encontrada'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        if (!$this->matriculaService->canEditMatricula($user, $matricula)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        $data = json_decode($request->getContent(), true);
        $matricula = $this->matriculaService->updateMatricula($matricula, $data);
        $estudiante = $matricula->getEstudiante();
        $curso = $matricula->getCurso();
        $periodo = $matricula->getPeriodo();
        return $this->json([
            'id_matricula' => $matricula->getIdMatricula(),
            'id_estudiante' => $estudiante?->getId(),
            'id_curso' => $curso?->getIdCurso(),
            'id_periodo' => $periodo?->getIdPeriodo(),
            'estado' => $matricula->isEstado(),
            'fecha_matricula' => $matricula->getFechaMatricula()?->format('Y-m-d'),
            'estudiante' => $estudiante ? ($estudiante->getNombres() . ' ' . $estudiante->getApellidos()) : null,
            'curso' => $curso ? $curso->getNombreCurso() : null,
            'periodo' => $periodo ? $periodo->getDescripcion() : null,
        ]);
    }

    // Eliminar matrícula
    #[Route('/api/matriculas/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $matricula = $this->matriculaService->getMatriculaById($id);
        if (!$matricula) {
            //Matrícula no encontrada
            return $this->json(['error' => 'Matrícula no encontrada'], Response::HTTP_NOT_FOUND);
        }
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        if (!$this->matriculaService->canEditMatricula($user, $matricula)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        $this->matriculaService->deleteMatricula($matricula);
        //Matrícula eliminada correctamente
        return $this->json(['message' => 'Matrícula eliminada correctamente']);
    }
}
