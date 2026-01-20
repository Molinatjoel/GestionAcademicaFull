<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\Rol;
use App\Interface\UserServiceInterface;
use App\Repository\UserRepository;
use App\Repository\UserRolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService implements UserServiceInterface
{
    private UserRepository $userRepository;
    private UserRolRepository $userRolRepository;
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        UserRepository $userRepository, 
        UserRolRepository $userRolRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->userRolRepository = $userRolRepository;
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    public function createUser(array $data): User
    {
        $user = new User();
        
        // Asignar datos obligatorios
        $user->setNombres($data['nombres'] ?? '');
        $user->setApellidos($data['apellidos'] ?? '');
        $user->setCorreo($data['correo'] ?? '');
        $user->setDireccion($data['direccion'] ?? '');
        $user->setTelefono($data['telefono'] ?? '');
        $user->setEstado($data['estado'] ?? true);
        
        // Fecha de nacimiento
        if (isset($data['fecha_nacimiento'])) {
            $fechaNacimiento = is_string($data['fecha_nacimiento']) 
                ? new \DateTime($data['fecha_nacimiento']) 
                : $data['fecha_nacimiento'];
            $user->setFechaNacimiento($fechaNacimiento);
        } else {
            $user->setFechaNacimiento(new \DateTime());
        }
        
        // Hashear contraseña
        if (isset($data['contrasena']) && !empty($data['contrasena'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['contrasena']);
            $user->setPassword($hashedPassword);
        } elseif (isset($data['password']) && !empty($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }
        
        // Fechas de auditoría
        $user->setFechaCreacion(new \DateTime());
        $user->setFechaActualizacion(new \DateTime());
        
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        // Actualizar datos del usuario
        if (isset($data['nombres'])) {
            $user->setNombres($data['nombres']);
        }
        if (isset($data['apellidos'])) {
            $user->setApellidos($data['apellidos']);
        }
        if (isset($data['correo'])) {
            $user->setCorreo($data['correo']);
        }
        if (isset($data['direccion'])) {
            $user->setDireccion($data['direccion']);
        }
        if (isset($data['telefono'])) {
            $user->setTelefono($data['telefono']);
        }
        if (isset($data['fecha_nacimiento'])) {
            $fechaNacimiento = is_string($data['fecha_nacimiento']) 
                ? new \DateTime($data['fecha_nacimiento']) 
                : $data['fecha_nacimiento'];
            $user->setFechaNacimiento($fechaNacimiento);
        }
        if (isset($data['estado'])) {
            $user->setEstado($data['estado']);
        }
        
        // Actualizar contraseña si se proporciona
        if (isset($data['contrasena']) && !empty($data['contrasena'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['contrasena']);
            $user->setPassword($hashedPassword);
        } elseif (isset($data['password']) && !empty($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }
        
        $user->setFechaActualizacion(new \DateTime());
        
        $this->em->flush();
        return $user;
    }

    public function deleteUser(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    /**
     * @return Rol[]
     */
    public function getUserRoles(int $userId): array
    {
        $userRoles = $this->userRolRepository->findBy(['user' => $userId]);
        return array_map(fn($ur) => $ur->getRol(), $userRoles);
    }
}
