<?php
namespace App\Controller;

use App\Service\RolService;
use App\Request\RolRequest;
use App\Entity\Rol;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RolController extends AbstractController
{
    private RolService $rolService;
    private ValidatorInterface $validator;

    // Inyección del servicio RolService y el validador
    public function __construct(RolService $rolService, ValidatorInterface $validator)
    {
        $this->rolService = $rolService;
        $this->validator = $validator;
    }

    // Crear rol
    #[Route('/api/roles', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $rolRequest = new RolRequest();
        $rolRequest->nombre_rol = $data['nombre_rol'] ?? '';
        $rolRequest->descripcion = $data['descripcion'] ?? null;
        $errors = $this->validator->validate($rolRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            //Errores de validación
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $rol = $this->rolService->createRol($data);
        return $this->json([
            'id_rol' => $rol->getIdRol(),
            'nombre_rol' => $rol->getNombreRol(),
            'descripcion' => $rol->getDescripcion(),
        ]);
    }

    // Listar todos los roles
    #[Route('/api/roles', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $roles = $this->rolService->getAllRoles();
        $payload = array_map(static function (Rol $rol) {
            return [
                'id_rol' => $rol->getIdRol(),
                'nombre_rol' => $rol->getNombreRol(),
                'descripcion' => $rol->getDescripcion(),
            ];
        }, $roles);
        return $this->json($payload);
    }

    // Obtener rol por ID
    #[Route('/api/roles/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $rol = $this->rolService->getRolById($id);
        if (!$rol) {
            //Rol no encontrado
            return $this->json(['error' => 'Rol no encontrado'], Response::HTTP_NOT_FOUND);
        }
        return $this->json([
            'id_rol' => $rol->getIdRol(),
            'nombre_rol' => $rol->getNombreRol(),
            'descripcion' => $rol->getDescripcion(),
        ]);
    }

    // Actualizar rol
    #[Route('/api/roles/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $rol = $this->rolService->getRolById($id);
        if (!$rol) {
            //Rol no encontrado
            return $this->json(['error' => 'Rol no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        $rol = $this->rolService->updateRol($rol, $data);
        return $this->json($rol);
    }

    // Eliminar rol
    #[Route('/api/roles/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $rol = $this->rolService->getRolById($id);
        if (!$rol) {
            //Rol no encontrado
            return $this->json(['error' => 'Rol no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $this->rolService->deleteRol($rol);
        //Rol eliminado correctamente
        return $this->json(['message' => 'Rol eliminado correctamente']);
    }
}
