<?php
namespace App\Controller;

use App\Service\AsignaturaService;
use App\Request\AsignaturaRequest;
use App\Entity\Asignatura;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AsignaturaController extends AbstractController
{
    private AsignaturaService $asignaturaService;
    private ValidatorInterface $validator;

    // Inyección del servicio AsignaturaService y el validador
    public function __construct(AsignaturaService $asignaturaService, ValidatorInterface $validator)
    {
        $this->asignaturaService = $asignaturaService;
        $this->validator = $validator;
    }

    // Crear asignatura
    #[Route('/api/asignaturas', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $asignaturaRequest = new AsignaturaRequest();
        $asignaturaRequest->nombre_asignatura = $data['nombre_asignatura'] ?? '';
        $asignaturaRequest->descripcion = $data['descripcion'] ?? null;
        $errors = $this->validator->validate($asignaturaRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            //Errores de validación
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $asignatura = $this->asignaturaService->createAsignatura($data);
        return $this->json([
            'id_asignatura' => $asignatura->getIdAsignatura(),
            'nombre_asignatura' => $asignatura->getNombreAsignatura(),
            'descripcion' => $asignatura->getDescripcion(),
        ]);
    }

    // Listar todas las asignaturas
    #[Route('/api/asignaturas', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $asignaturas = $this->asignaturaService->getAllAsignaturas();
        $payload = array_map(static function (Asignatura $asignatura) {
            return [
                'id_asignatura' => $asignatura->getIdAsignatura(),
                'nombre_asignatura' => $asignatura->getNombreAsignatura(),
                'descripcion' => $asignatura->getDescripcion(),
            ];
        }, $asignaturas);
        return $this->json($payload);
    }

    // Obtener asignatura por ID
    #[Route('/api/asignaturas/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $asignatura = $this->asignaturaService->getAsignaturaById($id);
        if (!$asignatura) {
            //Asignatura no encontrada
            return $this->json(['error' => 'Asignatura no encontrada'], Response::HTTP_NOT_FOUND);
        }
        return $this->json([
            'id_asignatura' => $asignatura->getIdAsignatura(),
            'nombre_asignatura' => $asignatura->getNombreAsignatura(),
            'descripcion' => $asignatura->getDescripcion(),
        ]);
    }

    // Actualizar asignatura
    #[Route('/api/asignaturas/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $asignatura = $this->asignaturaService->getAsignaturaById($id);
        if (!$asignatura) {
            //Asignatura no encontrada
            return $this->json(['error' => 'Asignatura no encontrada'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        $asignatura = $this->asignaturaService->updateAsignatura($asignatura, $data);
        return $this->json([
            'id_asignatura' => $asignatura->getIdAsignatura(),
            'nombre_asignatura' => $asignatura->getNombreAsignatura(),
            'descripcion' => $asignatura->getDescripcion(),
        ]);
    }

    // Eliminar asignatura
    #[Route('/api/asignaturas/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $asignatura = $this->asignaturaService->getAsignaturaById($id);
        if (!$asignatura) {
            //Asignatura no encontrada
            return $this->json(['error' => 'Asignatura no encontrada'], Response::HTTP_NOT_FOUND);
        }
        $this->asignaturaService->deleteAsignatura($asignatura);
        //Asignatura eliminada correctamente
        return $this->json(['message' => 'Asignatura eliminada correctamente']);
    }
}
