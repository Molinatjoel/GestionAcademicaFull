<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Rol;
use App\Entity\UserRol;
use App\Repository\UserRepository;
use App\Repository\RolRepository;
use App\Repository\UserRolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private RolRepository $rolRepository;
    private UserRolRepository $userRolRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private string $jwtSecret;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        RolRepository $rolRepository,
        UserRolRepository $userRolRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->rolRepository = $rolRepository;
        $this->userRolRepository = $userRolRepository;
        $this->passwordHasher = $passwordHasher;
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'clave_jwt_default';
    }

    // Registro de usuario
    #[Route('/api/register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Validar campos requeridos
        $correo = $data['correo'] ?? null;
        $password = $data['password'] ?? null;
        $nombres = $data['nombres'] ?? null;
        $apellidos = $data['apellidos'] ?? null;
        
        if (!$correo || !$password || !$nombres || !$apellidos) {
            return $this->json(['error' => 'Datos incompletos (correo, password, nombres, apellidos son requeridos)'], Response::HTTP_BAD_REQUEST);
        }
        
        // Verificar si el correo ya existe
        if ($this->userRepository->findOneBy(['correo' => $correo])) {
            return $this->json(['error' => 'El correo ya está registrado'], Response::HTTP_CONFLICT);
        }
        
        // Crear usuario
        $user = new User();
        $user->setCorreo($correo);
        $user->setNombres($nombres);
        $user->setApellidos($apellidos);
        $user->setDireccion($data['direccion'] ?? '');
        $user->setTelefono($data['telefono'] ?? '');
        $user->setFechaNacimiento(isset($data['fecha_nacimiento']) ? new \DateTime($data['fecha_nacimiento']) : new \DateTime());
        $user->setEstado(true);
        $user->setFechaCreacion(new \DateTime());
        $user->setFechaActualizacion(new \DateTime());
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        
        $this->em->persist($user);
        $this->em->flush();
        
        // Asignar rol por defecto 'estudiante'
        $rol = $this->rolRepository->findOneBy(['nombre_rol' => 'estudiante']);
        if ($rol) {
            $userRol = new UserRol();
            $userRol->setUser($user);
            $userRol->setRol($rol);
            $this->em->persist($userRol);
            $this->em->flush();
        }
        
        return $this->json(['message' => 'Usuario registrado correctamente', 'id' => $user->getId()]);
    }

    // Login de usuario
    #[Route('/api/login', methods: ['POST'])]
    #[Route('/api/auth/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $correo = $data['correo'] ?? null;
        $password = $data['password'] ?? null;
        
        if (!$correo || !$password) {
            return $this->json(['error' => 'Correo y password son requeridos'], Response::HTTP_BAD_REQUEST);
        }
        
        $user = $this->userRepository->findOneBy(['correo' => $correo]);
        
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['error' => 'Credenciales inválidas'], Response::HTTP_UNAUTHORIZED);
        }
        
        // Verificar si el usuario está activo
        if (!$user->isEstado()) {
            return $this->json(['error' => 'Usuario inactivo'], Response::HTTP_FORBIDDEN);
        }
        
        // Obtener roles del usuario
        $userRoles = $this->userRolRepository->findBy(['user' => $user]);
        $roles = [];
        foreach ($userRoles as $userRol) {
            $roles[] = $userRol->getRol()->getNombreRol();
        }
        
        // Generar JWT
        $payload = [
            'sub' => $user->getCorreo(), // identificador de usuario para seguridad
            'uid' => $user->getId(),
            'nombres' => $user->getNombres(),
            'apellidos' => $user->getApellidos(),
            'roles' => $roles,
            'exp' => time() + 3600 // Expira en 1 hora
        ];
        
        $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');
        
        return $this->json([
            'token' => $jwt,
            'user' => [
                'id' => $user->getId(),
                'correo' => $user->getCorreo(),
                'nombres' => $user->getNombres(),
                'apellidos' => $user->getApellidos(),
                'roles' => $roles
            ]
        ]);
    }
}
