<?php
namespace App\Service;

use App\Entity\Chat;
use App\Entity\User;
use App\Interface\ChatServiceInterface;
use App\Repository\ChatRepository;
use App\Repository\ChatUserRepository;
use App\Repository\CursoRepository;
use App\Repository\UserRepository;
use App\Repository\MatriculaRepository;
use App\Repository\CursoAsignaturaRepository;
use App\Repository\DatosFamiliaresRepository;
use App\Entity\Curso;
use Doctrine\ORM\EntityManagerInterface;

class ChatService implements ChatServiceInterface
{
    private ChatRepository $chatRepository;
    private ChatUserRepository $chatUserRepository;
    private CursoRepository $cursoRepository;
    private UserRepository $userRepository;
    private MatriculaRepository $matriculaRepository;
    private CursoAsignaturaRepository $cursoAsignaturaRepository;
    private DatosFamiliaresRepository $datosFamiliaresRepository;
    private EntityManagerInterface $em;

    public function __construct(
        ChatRepository $chatRepository,
        ChatUserRepository $chatUserRepository,
        CursoRepository $cursoRepository,
        UserRepository $userRepository,
        MatriculaRepository $matriculaRepository,
        CursoAsignaturaRepository $cursoAsignaturaRepository,
        DatosFamiliaresRepository $datosFamiliaresRepository,
        EntityManagerInterface $em
    ) {
        $this->chatRepository = $chatRepository;
        $this->chatUserRepository = $chatUserRepository;
        $this->cursoRepository = $cursoRepository;
        $this->userRepository = $userRepository;
        $this->matriculaRepository = $matriculaRepository;
        $this->cursoAsignaturaRepository = $cursoAsignaturaRepository;
        $this->datosFamiliaresRepository = $datosFamiliaresRepository;
        $this->em = $em;
    }

    public function createChat(array $data): Chat
    {
        $chat = new Chat();
        $chat->setTitulo($data['nombre'] ?? $data['titulo'] ?? '');
        $chat->setTipo($data['tipo'] ?? 'general');

        if (isset($data['id_curso'])) {
            $curso = $this->cursoRepository->find($data['id_curso']);
            $chat->setCurso($curso);
        }

        if (isset($data['id_creador'])) {
            $creador = $this->userRepository->find($data['id_creador']);
            $chat->setCreador($creador);
        }
        $chat->setFechaCreacion(new \DateTime());
        
        $this->em->persist($chat);
        $this->em->flush();
        return $chat;
    }

    public function updateChat(Chat $chat, array $data): Chat
    {
        if (isset($data['nombre'])) {
            $chat->setTitulo($data['nombre']);
        } elseif (isset($data['titulo'])) {
            $chat->setTitulo($data['titulo']);
        }
        
        $this->em->flush();
        return $chat;
    }

    public function deleteChat(Chat $chat): void
    {
        $this->em->remove($chat);
        $this->em->flush();
    }

    public function getChatById(int $id): ?Chat
    {
        return $this->chatRepository->find($id);
    }

    public function getAllChats(): array
    {
        return $this->chatRepository->findAll();
    }

    public function getParticipants(Chat $chat): array
    {
        $links = $this->chatUserRepository->findBy(['chat' => $chat]);
        $seen = [];
        $result = [];
        foreach ($links as $cu) {
            $u = $cu->getUser();
            if (!$u || !$u->getId() || isset($seen[$u->getId()])) {
                continue;
            }
            $seen[$u->getId()] = true;
            $result[] = [
                'id' => $u->getId(),
                'nombre' => trim(($u->getNombres() ?? '') . ' ' . ($u->getApellidos() ?? '')),
                'correo' => $u->getCorreo(),
                'roles' => $u->getRoles(),
            ];
        }
        return $result;
    }

    public function getVisibleChatsForUser(User $user): array
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return $this->chatRepository->findAll();
        }

        $qb = $this->chatRepository->createQueryBuilder('c')
            ->innerJoin('App\\Entity\\ChatUser', 'cu', 'WITH', 'cu.chat = c.id_chat')
            ->andWhere('cu.user = :uid')
            ->setParameter('uid', $user->getId());

        return $qb->getQuery()->getResult();
    }

    public function canViewChat(User $user, Chat $chat): bool
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return true;
        }

        return $this->chatUserRepository->findOneBy(['chat' => $chat, 'user' => $user]) !== null;
    }

    public function ensureGroupChatForCurso(int $cursoId, ?User $requester = null, bool $skipAccessCheck = false): Chat
    {
        $curso = $this->cursoRepository->find($cursoId);
        if (!$curso) {
            throw new \InvalidArgumentException('Curso no encontrado');
        }

        if (!$skipAccessCheck && $requester && !$this->userCanJoinCursoChat($requester, $curso)) {
            throw new \RuntimeException('No autorizado para el chat de este curso');
        }

        $chat = $this->chatRepository->createQueryBuilder('c')
            ->andWhere('c.curso = :curso')
            ->andWhere('c.tipo = :tipo')
            ->setParameter('curso', $curso)
            ->setParameter('tipo', 'curso')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$chat) {
            $chat = new Chat();
            $chat->setTitulo('Chat curso ' . $curso->getNombreCurso());
            $chat->setTipo('curso');
            $chat->setCurso($curso);
            $chat->setCreador($requester);
            $chat->setFechaCreacion(new \DateTime());
            $this->em->persist($chat);
            $this->em->flush();
        }

        $members = $this->collectCursoMembers($curso);
        if ($requester) {
            $members[] = $requester;
        }
        $this->addMembers($chat, $members);

        return $chat;
    }

    public function ensurePrivateChat(User $requester, int $targetUserId): Chat
    {
        $target = $this->userRepository->find($targetUserId);
        if (!$target) {
            throw new \InvalidArgumentException('Usuario destino no encontrado');
        }

        if (!$this->canStartPrivateWith($requester, $target)) {
            throw new \RuntimeException('No autorizado para chatear con este usuario');
        }

        $chat = $this->chatRepository->createQueryBuilder('c')
            ->innerJoin('App\\Entity\\ChatUser', 'cu1', 'WITH', 'cu1.chat = c.id_chat')
            ->innerJoin('App\\Entity\\ChatUser', 'cu2', 'WITH', 'cu2.chat = c.id_chat')
            ->andWhere('c.tipo = :tipo')
            ->andWhere('cu1.user = :u1')
            ->andWhere('cu2.user = :u2')
            ->setParameter('tipo', 'privado')
            ->setParameter('u1', $requester->getId())
            ->setParameter('u2', $target->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$chat) {
            $chat = new Chat();
            $chat->setTitulo('Chat privado');
            $chat->setTipo('privado');
            $chat->setCreador($requester);
            $chat->setFechaCreacion(new \DateTime());
            $this->em->persist($chat);
            $this->em->flush();
        }

        $this->addMembers($chat, [$requester, $target]);

        return $chat;
    }

    private function addMembers(Chat $chat, array $users): void
    {
        $ids = [];
        foreach ($users as $u) {
            if (!$u) { continue; }
            $uid = $u->getId();
            if (!$uid || isset($ids[$uid])) { continue; }
            $ids[$uid] = true;
            $existing = $this->chatUserRepository->findOneBy(['chat' => $chat, 'user' => $u]);
            if ($existing) { continue; }

            $cu = new \App\Entity\ChatUser();
            $cu->setChat($chat);
            $cu->setUser($u);
            $cu->setFechaUnion(new \DateTime());
            $this->em->persist($cu);
        }
        $this->em->flush();
    }

    private function collectCursoMembers(Curso $curso): array
    {
        $members = [];
        $matriculas = $this->matriculaRepository->findBy(['curso' => $curso]);
        foreach ($matriculas as $m) {
            $student = $m->getEstudiante();
            if ($student) { $members[] = $student; }
        }

        $cas = $this->cursoAsignaturaRepository->findBy(['curso' => $curso]);
        foreach ($cas as $ca) {
            $doc = $ca->getDocente();
            if ($doc) { $members[] = $doc; }
        }

        $titular = $curso->getDocenteTitular();
        if ($titular) { $members[] = $titular; }

        return $members;
    }

    private function canStartPrivateWith(User $requester, User $target): bool
    {
        $roles = $requester->getRoles();
        if (in_array('ROLE_ADMIN', $roles, true) || in_array('ROLE_DOCENTE', $roles, true)) {
            return true;
        }

        // Estudiante: docentes de sus asignaturas o compañeros de curso
        if (in_array('ROLE_ESTUDIANTE', $roles, true)) {
            if ($this->userIsDocenteDeEstudiante($target, $requester)) {
                return true;
            }
            return $this->userSharesCurso($requester, $target);
        }

        // Padre/representante: con docentes que dan clase a sus representados
        if (in_array('ROLE_PADRE', $roles, true) || in_array('ROLE_REPRESENTANTE', $roles, true)) {
            $hijos = $this->datosFamiliaresRepository->findBy(['representanteUser' => $requester]);
            foreach ($hijos as $df) {
                $est = $df->getEstudiante();
                if ($est && $this->userIsDocenteDeEstudiante($target, $est)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    public function searchUsersForChat(User $requester, string $term = '', ?int $cursoId = null): array
    {
        $roles = $requester->getRoles();
        $termLike = '%' . trim($term) . '%';

        if (in_array('ROLE_ADMIN', $roles, true) || in_array('ROLE_DOCENTE', $roles, true)) {
            return $this->searchAllUsers($termLike, $requester->getId());
        }

        if (in_array('ROLE_ESTUDIANTE', $roles, true)) {
            $ids = $this->collectUsuariosPermitidosParaEstudiante($requester, $cursoId);
            return $this->searchUsersByIds($ids, $termLike, $requester->getId());
        }

        if (in_array('ROLE_PADRE', $roles, true) || in_array('ROLE_REPRESENTANTE', $roles, true)) {
            $ids = $this->collectDocentesDeHijos($requester);
            return $this->searchUsersByIds($ids, $termLike, $requester->getId());
        }

        throw new \RuntimeException('Rol no autorizado');
    }

    public function removeFromCursoChat(Curso $curso, User $user): void
    {
        $chat = $this->chatRepository->findOneBy(['curso' => $curso, 'tipo' => 'curso']);
        if (!$chat) {
            return;
        }
        $cu = $this->chatUserRepository->findOneBy(['chat' => $chat, 'user' => $user]);
        if ($cu) {
            $this->em->remove($cu);
            $this->em->flush();
        }
    }

    public function syncCursoChatMembers(Curso $curso): void
    {
        $chat = $this->chatRepository->findOneBy(['curso' => $curso, 'tipo' => 'curso']);
        if (!$chat) {
            return;
        }
        $members = $this->collectCursoMembers($curso);
        $this->addMembers($chat, $members);
    }

    private function userIsDocenteDeEstudiante(User $docente, User $estudiante): bool
    {
        $matriculas = $this->matriculaRepository->findBy(['estudiante' => $estudiante]);
        if (empty($matriculas)) {
            return false;
        }
        foreach ($matriculas as $m) {
            $curso = $m->getCurso();
            if (!$curso) { continue; }
            $cas = $this->cursoAsignaturaRepository->findBy(['curso' => $curso]);
            foreach ($cas as $ca) {
                $doc = $ca->getDocente();
                if ($doc && $doc->getId() === $docente->getId()) {
                    return true;
                }
            }
        }
        return false;
    }

    private function userSharesCurso(User $estudiante, User $target): bool
    {
        $matriculas = $this->matriculaRepository->findBy(['estudiante' => $estudiante]);
        if (empty($matriculas)) {
            return false;
        }
        foreach ($matriculas as $m) {
            $curso = $m->getCurso();
            if (!$curso) { continue; }
            $mat = $this->matriculaRepository->findOneBy(['curso' => $curso, 'estudiante' => $target]);
            if ($mat) {
                return true;
            }
        }
        return false;
    }

    private function userCanJoinCursoChat(User $user, Curso $curso): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        // Docente del curso
        $cas = $this->cursoAsignaturaRepository->findBy(['curso' => $curso]);
        foreach ($cas as $ca) {
            $doc = $ca->getDocente();
            if ($doc && $doc->getId() === $user->getId()) {
                return true;
            }
        }
        $titular = $curso->getDocenteTitular();
        if ($titular && $titular->getId() === $user->getId()) {
            return true;
        }

        // Estudiante matriculado
        $mat = $this->matriculaRepository->findOneBy(['curso' => $curso, 'estudiante' => $user]);
        if ($mat) {
            return true;
        }

        // Padre de estudiante del curso
        $dflist = $this->datosFamiliaresRepository->findBy(['representanteUser' => $user]);
        foreach ($dflist as $df) {
            $est = $df->getEstudiante();
            if (!$est) { continue; }
            $mat = $this->matriculaRepository->findOneBy(['curso' => $curso, 'estudiante' => $est]);
            if ($mat) {
                return true;
            }
        }

        return false;
    }

    private function searchAllUsers(string $termLike, int $excludeId): array
    {
        $qb = $this->userRepository->createQueryBuilder('u')
            ->where('u.id != :uid')
            ->setParameter('uid', $excludeId);

        if (trim($termLike, '%') !== '') {
            $qb->andWhere('(u.nombres LIKE :q OR u.apellidos LIKE :q OR u.correo LIKE :q)')
               ->setParameter('q', $termLike);
        }

        return array_map([$this, 'mapUser'], $qb->getQuery()->getResult());
    }

    private function searchUsersByIds(array $ids, string $termLike, int $excludeId): array
    {
        if (empty($ids)) {
            return [];
        }
        $qb = $this->userRepository->createQueryBuilder('u')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', array_values(array_unique($ids)))
            ->andWhere('u.id != :uid')
            ->setParameter('uid', $excludeId);

        if (trim($termLike, '%') !== '') {
            $qb->andWhere('(u.nombres LIKE :q OR u.apellidos LIKE :q OR u.correo LIKE :q)')
               ->setParameter('q', $termLike);
        }

        return array_map([$this, 'mapUser'], $qb->getQuery()->getResult());
    }

    private function collectUsuariosPermitidosParaEstudiante(User $estudiante, ?int $cursoFiltro): array
    {
        $ids = [];
        $matriculas = $this->matriculaRepository->findBy(['estudiante' => $estudiante]);
        foreach ($matriculas as $m) {
            $curso = $m->getCurso();
            if (!$curso) { continue; }
            if ($cursoFiltro && $curso->getIdCurso() !== $cursoFiltro) { continue; }

            // compañeros del curso
            $compas = $this->matriculaRepository->findBy(['curso' => $curso]);
            foreach ($compas as $compa) {
                $ids[] = $compa->getEstudiante()?->getId();
            }

            // docentes del curso
            $cas = $this->cursoAsignaturaRepository->findBy(['curso' => $curso]);
            foreach ($cas as $ca) {
                $ids[] = $ca->getDocente()?->getId();
            }

            $titular = $curso->getDocenteTitular();
            if ($titular) {
                $ids[] = $titular->getId();
            }
        }

        return array_values(array_filter(array_unique($ids)));
    }

    private function collectDocentesDeHijos(User $padre): array
    {
        $ids = [];
        $hijos = $this->datosFamiliaresRepository->findBy(['representanteUser' => $padre]);
        foreach ($hijos as $df) {
            $est = $df->getEstudiante();
            if (!$est) { continue; }
            $matriculas = $this->matriculaRepository->findBy(['estudiante' => $est]);
            foreach ($matriculas as $m) {
                $curso = $m->getCurso();
                if (!$curso) { continue; }
                $cas = $this->cursoAsignaturaRepository->findBy(['curso' => $curso]);
                foreach ($cas as $ca) {
                    $ids[] = $ca->getDocente()?->getId();
                }
                $titular = $curso->getDocenteTitular();
                if ($titular) {
                    $ids[] = $titular->getId();
                }
            }
        }
        return array_values(array_filter(array_unique($ids)));
    }

    private function mapUser(User $u): array
    {
        return [
            'id' => $u->getId(),
            'nombre' => trim(($u->getNombres() ?? '') . ' ' . ($u->getApellidos() ?? '')),
            'correo' => $u->getCorreo(),
            'roles' => $u->getRoles(),
        ];
    }
}
