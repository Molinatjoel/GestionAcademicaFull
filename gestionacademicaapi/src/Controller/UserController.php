<?php
namespace App\Controller;

use App\Service\UserService;
use App\Request\UserRequest;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    private UserService $userService;
    private ValidatorInterface $validator;

    // Inyección del servicio UserService y el validador
    public function __construct(UserService $userService, ValidatorInterface $validator)
    {
        $this->userService = $userService;
        $this->validator = $validator;
    }

    // Crear usuario
    #[Route('/api/usuarios', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Obtener datos del request
        $data = json_decode($request->getContent(), true);

        // Mapear datos a UserRequest
        $userRequest = new UserRequest();
        $userRequest->nombres = $data['nombres'] ?? '';
        $userRequest->apellidos = $data['apellidos'] ?? '';
        $userRequest->fecha_nacimiento = isset($data['fecha_nacimiento']) ? new \DateTime($data['fecha_nacimiento']) : null;
        $userRequest->direccion = $data['direccion'] ?? '';
        $userRequest->correo = $data['correo'] ?? '';
        $userRequest->telefono = $data['telefono'] ?? '';
        $userRequest->contrasena = $data['contrasena'] ?? null;
        $userRequest->estado = $data['estado'] ?? true;
        $userRequest->fecha_creacion = new \DateTime();
        $userRequest->fecha_actualizacion = new \DateTime();

        // Validar datos
        $errors = $this->validator->validate($userRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            //Retornar errores de validación
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Requiere contraseña en creación de nuevo usuario
        if (empty($data['contrasena'])) {
            return $this->json(['error' => 'La contraseña es obligatoria para crear usuario'], Response::HTTP_BAD_REQUEST);
        }

        // Crear usuario si los datos son válidos
        $user = $this->userService->createUser($data);
        $roles = $this->userService->getUserRoles($user->getId());
        // Retornar respuesta
        return $this->json([
            'id' => $user->getId(),
            'correo' => $user->getCorreo(),
            'nombres' => $user->getNombres(),
            'apellidos' => $user->getApellidos(),
            'telefono' => $user->getTelefono(),
            'direccion' => $user->getDireccion(),
            'estado' => $user->isEstado(),
            'roles' => array_map(fn($r) => ['id_rol' => $r->getIdRol(), 'nombre_rol' => $r->getNombreRol()], $roles),
        ]);
    }

    // Listar todos los usuarios
    #[Route('/api/usuarios', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $users = $this->userService->getAllUsers();
        $payload = array_map(function (User $u) {
            $roles = $this->userService->getUserRoles($u->getId());
            return [
                'id' => $u->getId(),
                'correo' => $u->getCorreo(),
                'nombres' => $u->getNombres(),
                'apellidos' => $u->getApellidos(),
                'telefono' => $u->getTelefono(),
                'direccion' => $u->getDireccion(),
                'estado' => $u->isEstado(),
                'roles' => array_map(fn($r) => ['id_rol' => $r->getIdRol(), 'nombre_rol' => $r->getNombreRol()], $roles),
            ];
        }, $users);
        return $this->json($payload);
    }

    // Obtener usuario por ID
    #[Route('/api/usuarios/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            //Usuario no encontrado
            return $this->json(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($user);
    }

    // Actualizar usuario
    #[Route('/api/usuarios/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            //Usuario no encontrado
            return $this->json(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        $user = $this->userService->updateUser($user, $data);
        return $this->json($user);
    }

    // Eliminar usuario
    #[Route('/api/usuarios/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            //Usuario no encontrado
            return $this->json(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $this->userService->deleteUser($user);
        //Usuario eliminado correctamente
        return $this->json(['message' => 'Usuario eliminado correctamente']);
    }
}
