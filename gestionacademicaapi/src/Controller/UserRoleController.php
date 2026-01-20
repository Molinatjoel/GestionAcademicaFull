<?php
namespace App\Controller;

use App\Entity\User;
use App\Entity\Rol;
use App\Entity\UserRol;
use App\Repository\UserRepository;
use App\Repository\RolRepository;
use App\Repository\UserRolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserRoleController extends AbstractController
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;
    private RolRepository $rolRepository;
    private UserRolRepository $userRolRepository;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $userRepository,
        RolRepository $rolRepository,
        UserRolRepository $userRolRepository
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->rolRepository = $rolRepository;
        $this->userRolRepository = $userRolRepository;
    }

    // Asignar roles a usuario (solo admin)
    #[Route('/api/usuarios/{id}/roles', methods: ['PUT'])]
    public function assignRoles(Request $request, int $id): JsonResponse
    {
        $currentUser = $this->getUser();
        $roles = $currentUser ? $currentUser->getRoles() : [];

        if (!in_array('ROLE_ADMIN', $roles, true)) {
            return $this->json(['error' => 'Acceso denegado'], Response::HTTP_FORBIDDEN);
        }
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        $roles = $data['roles'] ?? [];
        if (empty($roles)) {
            return $this->json(['error' => 'Debe enviar al menos un rol'], Response::HTTP_BAD_REQUEST);
        }
        // Eliminar roles actuales
        $userRoles = $this->userRolRepository->findBy(['user' => $user]);
        foreach ($userRoles as $userRol) {
            $this->em->remove($userRol);
        }
        $this->em->flush();
        // Asignar nuevos roles
        foreach ($roles as $rolName) {
            $rol = $this->rolRepository->findOneBy(['nombre_rol' => $rolName]);
            if ($rol) {
                $userRol = new UserRol();
                $userRol->setUser($user);
                $userRol->setRol($rol);
                $this->em->persist($userRol);
            }
        }
        $this->em->flush();
        return $this->json(['message' => 'Roles asignados correctamente']);
    }
}
