<?php
namespace App\Command;

use App\Entity\User;
use App\Entity\Rol;
use App\Entity\UserRol;
use App\Entity\PeriodoLectivo;
use App\Entity\Curso;
use App\Entity\Asignatura;
use App\Entity\CursoAsignatura;
use App\Entity\Matricula;
use App\Entity\Calificacion;
use App\Entity\DatosFamiliares;
use App\Entity\Chat;
use App\Entity\ChatUser;
use App\Entity\Mensaje;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SeedDemoDataCommand extends Command
{
    public static function getDefaultName(): ?string
    {
        return 'app:seed-demo-data';
    }

    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this->setDescription('Crea datos de prueba: roles, usuarios (docente/estudiante/padre), curso, asignatura, matrícula, calificación, vínculos familiares y chat.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rolRepo = $this->em->getRepository(Rol::class);
        $userRepo = $this->em->getRepository(User::class);
        $userRolRepo = $this->em->getRepository(UserRol::class);
        $periodoRepo = $this->em->getRepository(PeriodoLectivo::class);
        $cursoRepo = $this->em->getRepository(Curso::class);
        $asignaturaRepo = $this->em->getRepository(Asignatura::class);
        $cursoAsignaturaRepo = $this->em->getRepository(CursoAsignatura::class);
        $matriculaRepo = $this->em->getRepository(Matricula::class);
        $calificacionRepo = $this->em->getRepository(Calificacion::class);
        $datosFamiliaresRepo = $this->em->getRepository(DatosFamiliares::class);
        $chatRepo = $this->em->getRepository(Chat::class);
        $chatUserRepo = $this->em->getRepository(ChatUser::class);
        $mensajeRepo = $this->em->getRepository(Mensaje::class);

        // Roles base
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

        // Usuarios de prueba
        $usersData = [
            ['correo' => 'docente@demo.com', 'nombres' => 'Dolores', 'apellidos' => 'Docente', 'rol' => 'docente'],
            ['correo' => 'estudiante@demo.com', 'nombres' => 'Esteban', 'apellidos' => 'Estudiante', 'rol' => 'estudiante'],
            ['correo' => 'padre@demo.com', 'nombres' => 'Pablo', 'apellidos' => 'Padre', 'rol' => 'padre'],
        ];
        $demoUsers = [];
        foreach ($usersData as $ud) {
            $user = $userRepo->findOneBy(['correo' => $ud['correo']]);
            if (!$user) {
                $user = new User();
                $user->setCorreo($ud['correo']);
                $user->setNombres($ud['nombres']);
                $user->setApellidos($ud['apellidos']);
                $user->setFechaNacimiento(new \DateTime('2000-01-01'));
                $user->setDireccion('Dirección demo');
                $user->setTelefono('0999999999');
                $user->setEstado(true);
                $user->setFechaCreacion(new \DateTime());
                $user->setFechaActualizacion(new \DateTime());
                $user->setPassword($this->passwordHasher->hashPassword($user, 'Demo12345'));
                $this->em->persist($user);
                $output->writeln("Usuario {$ud['correo']} creado.");
            }
            $demoUsers[$ud['rol']] = $user;

            $rol = $roles[$ud['rol']] ?? null;
            if ($rol) {
                $userRol = $userRolRepo->findOneBy(['user' => $user, 'rol' => $rol]);
                if (!$userRol) {
                    $userRol = new UserRol();
                    $userRol->setUser($user);
                    $userRol->setRol($rol);
                    $this->em->persist($userRol);
                    $output->writeln("Rol {$ud['rol']} asignado a {$ud['correo']}.");
                }
            }
        }

        $docente = $demoUsers['docente'];
        $estudiante = $demoUsers['estudiante'];
        $padre = $demoUsers['padre'];

        // Periodo lectivo
        $periodo = $periodoRepo->findOneBy(['descripcion' => '2025-2026']);
        if (!$periodo) {
            $periodo = new PeriodoLectivo();
            $periodo->setDescripcion('2025-2026');
            $periodo->setFechaInicio(new \DateTime('2025-04-01'));
            $periodo->setFechaFin(new \DateTime('2026-03-31'));
            $periodo->setEstado(true);
            $this->em->persist($periodo);
            $output->writeln('Periodo lectivo 2025-2026 creado.');
        }

        // Curso
        $curso = $cursoRepo->findOneBy(['nombre_curso' => 'Curso Demo A']);
        if (!$curso) {
            $curso = new Curso();
            $curso->setNombreCurso('Curso Demo A');
            $curso->setNivel('Primaria');
            $curso->setDocenteTitular($docente);
            $curso->setFechaCreacion(new \DateTime());
            $curso->setEstado(true);
            $this->em->persist($curso);
            $output->writeln('Curso Demo A creado.');
        }

        // Asignatura
        $asignatura = $asignaturaRepo->findOneBy(['nombre_asignatura' => 'Matemáticas']);
        if (!$asignatura) {
            $asignatura = new Asignatura();
            $asignatura->setNombreAsignatura('Matemáticas');
            $asignatura->setDescripcion('Matemáticas básicas');
            $this->em->persist($asignatura);
            $output->writeln('Asignatura Matemáticas creada.');
        }

        // CursoAsignatura
        $cursoAsignatura = $cursoAsignaturaRepo->findOneBy(['curso' => $curso, 'asignatura' => $asignatura]);
        if (!$cursoAsignatura) {
            $cursoAsignatura = new CursoAsignatura();
            $cursoAsignatura->setCurso($curso);
            $cursoAsignatura->setAsignatura($asignatura);
            $cursoAsignatura->setDocente($docente);
            $cursoAsignatura->setFechaCreacion(new \DateTime());
            $this->em->persist($cursoAsignatura);
            $output->writeln('CursoAsignatura vinculado a Matemáticas creado.');
        }

        // Matricula
        $matricula = $matriculaRepo->findOneBy([
            'estudiante' => $estudiante,
            'curso' => $curso,
            'periodo' => $periodo,
        ]);
        if (!$matricula) {
            $matricula = new Matricula();
            $matricula->setEstudiante($estudiante);
            $matricula->setCurso($curso);
            $matricula->setPeriodo($periodo);
            $matricula->setFechaMatricula(new \DateTime());
            $matricula->setEstado(true);
            $this->em->persist($matricula);
            $output->writeln('Matrícula de estudiante creada.');
        }

        // Calificacion
        $calificacion = $calificacionRepo->findOneBy([
            'matricula' => $matricula,
            'cursoAsignatura' => $cursoAsignatura,
        ]);
        if (!$calificacion) {
            $calificacion = new Calificacion();
            $calificacion->setMatricula($matricula);
            $calificacion->setCursoAsignatura($cursoAsignatura);
            $calificacion->setNota(9.50);
            $calificacion->setObservacion('Desempeño destacado');
            $calificacion->setFechaRegistro(new \DateTime());
            $this->em->persist($calificacion);
            $output->writeln('Calificación de ejemplo creada.');
        }

        // Datos familiares
        $datosFamiliares = $datosFamiliaresRepo->findOneBy(['estudiante' => $estudiante]);
        if (!$datosFamiliares) {
            $datosFamiliares = new DatosFamiliares();
            $datosFamiliares->setEstudiante($estudiante);
            $datosFamiliares->setNombrePadre('Padre Demo');
            $datosFamiliares->setTelefonoPadre('0888888888');
            $datosFamiliares->setNombreMadre('Madre Demo');
            $datosFamiliares->setTelefonoMadre('0777777777');
            $datosFamiliares->setDireccionFamiliar('Calle Demo 123');
            $datosFamiliares->setParentescoRepresentante('Padre');
            $datosFamiliares->setNombreRepresentante('Pablo Padre');
            $datosFamiliares->setOcupacionRepresentante('Independiente');
            $datosFamiliares->setTelefonoRepresentante('0666666666');
            $datosFamiliares->setRepresentanteUser($padre);
            $this->em->persist($datosFamiliares);
            $output->writeln('Datos familiares con representante creados.');
        }

        // Chat
        $chat = $chatRepo->findOneBy(['titulo' => 'Canal Curso Demo']);
        if (!$chat) {
            $chat = new Chat();
            $chat->setTitulo('Canal Curso Demo');
            $chat->setFechaCreacion(new \DateTime());
            $this->em->persist($chat);
            $output->writeln('Chat de curso demo creado.');
        }

        // Chat users (docente, estudiante, padre)
        foreach ([$docente, $estudiante, $padre] as $u) {
            $cu = $chatUserRepo->findOneBy(['chat' => $chat, 'user' => $u]);
            if (!$cu) {
                $cu = new ChatUser();
                $cu->setChat($chat);
                $cu->setUser($u);
                $cu->setFechaUnion(new \DateTime());
                $this->em->persist($cu);
                $output->writeln('Usuario ' . $u->getCorreo() . ' añadido al chat.');
            }
        }

        // Mensaje inicial
        $mensaje = $mensajeRepo->findOneBy(['chat' => $chat, 'emisor' => $docente]);
        if (!$mensaje) {
            $mensaje = new Mensaje();
            $mensaje->setChat($chat);
            $mensaje->setEmisor($docente);
            $mensaje->setContenido('Bienvenidos al curso demo.');
            $mensaje->setFechaEnvio(new \DateTime());
            $this->em->persist($mensaje);
            $output->writeln('Mensaje inicial creado.');
        }

        $this->em->flush();
        $output->writeln('Seed demo completado. Credenciales: docente@demo.com / Demo12345, estudiante@demo.com / Demo12345, padre@demo.com / Demo12345');

        return Command::SUCCESS;
    }
}
