<?php
namespace App\Command;

use App\Entity\User;
use App\Entity\Rol;
use App\Entity\UserRol;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SeedAdminUserCommand extends Command
{
    public static function getDefaultName(): ?string
    {
        return 'app:seed-admin-user';
    }
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure()
    {
        $this->setDescription('Crea un usuario admin y su rol para pruebas.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Crear roles si no existen
        $rolRepo = $this->em->getRepository(Rol::class);
        $rolesData = [
            ['admin', 'Rol administrador del sistema'],
            ['docente', 'Rol docente, puede registrar calificaciones y reportes'],
            ['estudiante', 'Rol estudiante, puede consultar sus calificaciones y materias'],
            ['padre', 'Rol padre/representante, puede consultar desempeño académico del estudiante'],
        ];
        $roles = [];
        foreach ($rolesData as [$nombre, $descripcion]) {
            $rol = $rolRepo->findOneBy(['nombre_rol' => $nombre]);
            if (!$rol) {
                $rol = new Rol();
                $rol->setNombreRol($nombre);
                $rol->setDescripcion($descripcion);
                $this->em->persist($rol);
                $output->writeln("Rol $nombre creado.");
            }
            $roles[$nombre] = $rol;
        }
        $adminRol = $roles['admin'];
        // Crear usuario admin si no existe
        $userRepo = $this->em->getRepository(User::class);
        $adminUser = $userRepo->findOneBy(['correo' => 'admin@admin.com']);
        if (!$adminUser) {
            $adminUser = new User();
            $adminUser->setCorreo('admin@admin.com');
            $adminUser->setNombres('Admin');
            $adminUser->setApellidos('Administrador');
            $adminUser->setFechaNacimiento(new \DateTime('1980-01-01'));
            $adminUser->setDireccion('Oficina principal');
            $adminUser->setTelefono('0000000000');
            $adminUser->setEstado(true);
            $adminUser->setFechaCreacion(new \DateTime());
            $adminUser->setFechaActualizacion(new \DateTime());
            $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin123'));
            $this->em->persist($adminUser);
            $output->writeln('Usuario admin creado.');
        }
        // Asignar rol admin al usuario
        $userRolRepo = $this->em->getRepository(UserRol::class);
        $userRol = $userRolRepo->findOneBy(['user' => $adminUser, 'rol' => $adminRol]);
        if (!$userRol) {
            $userRol = new UserRol();
            $userRol->setUser($adminUser);
            $userRol->setRol($adminRol);
            $this->em->persist($userRol);
            $output->writeln('Rol admin asignado al usuario admin.');
        }
        $this->em->flush();
        $output->writeln('Seed completado. Usuario: admin, Password: admin123');
        return Command::SUCCESS;
    }
}
