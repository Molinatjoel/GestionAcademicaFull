<?php
namespace App\Controller;

use App\Service\UserRolService;
use App\Request\UserRolRequest;
use App\Entity\UserRol;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserRolController extends AbstractController
{
    private UserRolService $userRolService;
    private ValidatorInterface $validator;

    // Inyección del servicio UserRolService y el validador
    public function __construct(UserRolService $userRolService, ValidatorInterface $validator)
    {
        $this->userRolService = $userRolService;
        $this->validator = $validator;
    }

    // Crear UserRol
    #[Route('/api/user-roles', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userRolRequest = new UserRolRequest();
        $userRolRequest->id_user = $data['id_user'] ?? null;
        $userRolRequest->id_rol = $data['id_rol'] ?? null;
        $errors = $this->validator->validate($userRolRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            //Errores de validación
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $userRol = $this->userRolService->createUserRol($data);
        return $this->json($userRol);
    }

    // Listar todos los UserRol
    #[Route('/api/user-roles', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $userRoles = $this->userRolService->getAllUserRoles();
        $payload = array_map(static function (UserRol $ur) {
            $user = $ur->getUser();
            $rol = $ur->getRol();

            return [
                'id_user_rol' => $ur->getIdUserRol(),
                'id_user' => $user?->getId(),
                'id_rol' => $rol?->getIdRol(),
                'correo' => $user?->getCorreo(),
                'nombre' => $user ? trim(($user->getNombres() ?? '') . ' ' . ($user->getApellidos() ?? '')) : null,
                'rol' => $rol?->getNombreRol(),
            ];
        }, $userRoles);

        return $this->json($payload);
    }

    // Obtener UserRol por ID
    #[Route('/api/user-roles/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $userRol = $this->userRolService->getUserRolById($id);
        if (!$userRol) {
            //UserRol no encontrado
            return $this->json(['error' => 'UserRol no encontrado'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($userRol);
    }

    // Actualizar UserRol
    #[Route('/api/user-roles/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $userRol = $this->userRolService->getUserRolById($id);
        if (!$userRol) {
            //UserRol no encontrado
            return $this->json(['error' => 'UserRol no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        $userRol = $this->userRolService->updateUserRol($userRol, $data);
        return $this->json($userRol);
    }

    // Eliminar UserRol
    #[Route('/api/user-roles/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $userRol = $this->userRolService->getUserRolById($id);
        if (!$userRol) {
            //UserRol no encontrado
            return $this->json(['error' => 'UserRol no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $this->userRolService->deleteUserRol($userRol);
        //UserRol eliminado correctamente
        return $this->json(['message' => 'UserRol eliminado correctamente']);
    }
}
