<?php
namespace App\Controller;

use App\Service\CursoService;
use App\Request\CursoRequest;
use App\Entity\Curso;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CursoController extends AbstractController
{
    private CursoService $cursoService;
    private ValidatorInterface $validator;

    // Inyección del servicio CursoService y el validador
    public function __construct(CursoService $cursoService, ValidatorInterface $validator)
    {
        $this->cursoService = $cursoService;
        $this->validator = $validator;
    }

    // Crear curso
    #[Route('/api/cursos', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $cursoRequest = new CursoRequest();
        $cursoRequest->nombre_curso = $data['nombre_curso'] ?? '';
        $cursoRequest->nivel = $data['nivel'] ?? '';
        $cursoRequest->id_docente_titular = $data['id_docente_titular'] ?? null;
        $cursoRequest->fecha_creacion = new \DateTime();
        $cursoRequest->estado = $data['estado'] ?? true;
        $errors = $this->validator->validate($cursoRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            //Errores de validación
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $curso = $this->cursoService->createCurso($data);
        return $this->json([
            'id_curso' => $curso->getIdCurso(),
            'nombre_curso' => $curso->getNombreCurso(),
            'nivel' => $curso->getNivel(),
            'estado' => $curso->isEstado(),
        ]);
    }

    // Listar todos los cursos
    #[Route('/api/cursos', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $cursos = $this->cursoService->getAllCursos();
        $payload = array_map(static function (Curso $curso) {
            return [
                'id_curso' => $curso->getIdCurso(),
                'nombre_curso' => $curso->getNombreCurso(),
                'nivel' => $curso->getNivel(),
                'estado' => $curso->isEstado(),
                'id_docente_titular' => $curso->getDocenteTitular()?->getId(),
            ];
        }, $cursos);
        return $this->json($payload);
    }

    // Obtener curso por ID
    #[Route('/api/cursos/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $curso = $this->cursoService->getCursoById($id);
        if (!$curso) {
            //Curso no encontrado
            return $this->json(['error' => 'Curso no encontrado'], Response::HTTP_NOT_FOUND);
        }
        return $this->json([
            'id_curso' => $curso->getIdCurso(),
            'nombre_curso' => $curso->getNombreCurso(),
            'nivel' => $curso->getNivel(),
            'estado' => $curso->isEstado(),
            'id_docente_titular' => $curso->getDocenteTitular()?->getId(),
        ]);
    }

    // Actualizar curso
    #[Route('/api/cursos/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $curso = $this->cursoService->getCursoById($id);
        if (!$curso) {
            //Curso no encontrado
            return $this->json(['error' => 'Curso no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        $curso = $this->cursoService->updateCurso($curso, $data);
        return $this->json([
            'id_curso' => $curso->getIdCurso(),
            'nombre_curso' => $curso->getNombreCurso(),
            'nivel' => $curso->getNivel(),
            'estado' => $curso->isEstado(),
        ]);
    }

    // Eliminar curso
    #[Route('/api/cursos/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $curso = $this->cursoService->getCursoById($id);
        if (!$curso) {
            //Curso no encontrado
            return $this->json(['error' => 'Curso no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $this->cursoService->deleteCurso($curso);
        //Curso eliminado correctamente
        return $this->json(['message' => 'Curso eliminado correctamente']);
    }
}
