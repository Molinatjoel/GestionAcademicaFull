<?php
namespace App\Controller;

use App\Service\MatriculaAsignaturaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MatriculaAsignaturaController extends AbstractController
{
    private MatriculaAsignaturaService $service;
    private Security $security;

    public function __construct(MatriculaAsignaturaService $service, Security $security)
    {
        $this->service = $service;
        $this->security = $security;
    }

    // List assignments by matricula
    #[Route('/api/matricula-asignaturas', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        $matriculaId = (int)($request->query->get('matricula_id') ?? 0);
        if ($matriculaId <= 0) {
            return $this->json(['error' => 'matricula_id es requerido'], Response::HTTP_BAD_REQUEST);
        }
        $list = $this->service->listByMatricula($matriculaId);
        return $this->json($list);
    }

    // Bulk set assignments for a matricula
    #[Route('/api/matricula-asignaturas/bulk', methods: ['POST'])]
    public function bulk(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }
        $data = json_decode($request->getContent(), true) ?? [];
        $matriculaId = (int)($data['matricula_id'] ?? 0);
        $ids = is_array($data['curso_asignatura_ids'] ?? null) ? $data['curso_asignatura_ids'] : [];
        if ($matriculaId <= 0) {
            return $this->json(['error' => 'matricula_id es requerido'], Response::HTTP_BAD_REQUEST);
        }
        $result = $this->service->setAsignaturasForMatricula($matriculaId, $ids);
        return $this->json($result);
    }
}
