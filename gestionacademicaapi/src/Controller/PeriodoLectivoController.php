<?php
namespace App\Controller;

use App\Service\PeriodoLectivoService;
use App\Request\PeriodoLectivoRequest;
use App\Entity\PeriodoLectivo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PeriodoLectivoController extends AbstractController
{
    private PeriodoLectivoService $periodoLectivoService;
    private ValidatorInterface $validator;

    // Inyección del servicio PeriodoLectivoService y el validador
    public function __construct(PeriodoLectivoService $periodoLectivoService, ValidatorInterface $validator)
    {
        $this->periodoLectivoService = $periodoLectivoService;
        $this->validator = $validator;
    }

    // Crear periodo lectivo
    #[Route('/api/periodos-lectivos', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $plRequest = new PeriodoLectivoRequest();
        $plRequest->descripcion = $data['descripcion'] ?? null;
        $plRequest->fecha_inicio = isset($data['fecha_inicio']) ? new \DateTime($data['fecha_inicio']) : null;
        $plRequest->fecha_fin = isset($data['fecha_fin']) ? new \DateTime($data['fecha_fin']) : null;
        $plRequest->estado = $data['estado'] ?? true;
        $errors = $this->validator->validate($plRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            //Errores de validación
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $periodoLectivo = $this->periodoLectivoService->createPeriodoLectivo($data);
        return $this->json([
            'id_periodo' => $periodoLectivo->getIdPeriodo(),
            'descripcion' => $periodoLectivo->getDescripcion(),
            'fecha_inicio' => $periodoLectivo->getFechaInicio()?->format('Y-m-d'),
            'fecha_fin' => $periodoLectivo->getFechaFin()?->format('Y-m-d'),
            'estado' => $periodoLectivo->isEstado(),
        ]);
    }

    // Listar todos los periodos lectivos
    #[Route('/api/periodos-lectivos', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $periodos = $this->periodoLectivoService->getAllPeriodoLectivo();
        $payload = array_map(static function (PeriodoLectivo $p) {
            return [
                'id_periodo' => $p->getIdPeriodo(),
                'descripcion' => $p->getDescripcion(),
                'fecha_inicio' => $p->getFechaInicio()?->format('Y-m-d'),
                'fecha_fin' => $p->getFechaFin()?->format('Y-m-d'),
                'estado' => $p->isEstado(),
            ];
        }, $periodos);
        return $this->json($payload);
    }

    // Obtener periodo lectivo por ID
    #[Route('/api/periodos-lectivos/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $periodoLectivo = $this->periodoLectivoService->getPeriodoLectivoById($id);
        if (!$periodoLectivo) {
            //Periodo lectivo no encontrado
            return $this->json(['error' => 'Periodo lectivo no encontrado'], Response::HTTP_NOT_FOUND);
        }
        return $this->json([
            'id_periodo' => $periodoLectivo->getIdPeriodo(),
            'descripcion' => $periodoLectivo->getDescripcion(),
            'fecha_inicio' => $periodoLectivo->getFechaInicio()?->format('Y-m-d'),
            'fecha_fin' => $periodoLectivo->getFechaFin()?->format('Y-m-d'),
            'estado' => $periodoLectivo->isEstado(),
        ]);
    }

    // Actualizar periodo lectivo
    #[Route('/api/periodos-lectivos/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $periodoLectivo = $this->periodoLectivoService->getPeriodoLectivoById($id);
        if (!$periodoLectivo) {
            //Periodo lectivo no encontrado
            return $this->json(['error' => 'Periodo lectivo no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        $periodoLectivo = $this->periodoLectivoService->updatePeriodoLectivo($periodoLectivo, $data);
        return $this->json([
            'id_periodo' => $periodoLectivo->getIdPeriodo(),
            'descripcion' => $periodoLectivo->getDescripcion(),
            'fecha_inicio' => $periodoLectivo->getFechaInicio()?->format('Y-m-d'),
            'fecha_fin' => $periodoLectivo->getFechaFin()?->format('Y-m-d'),
            'estado' => $periodoLectivo->isEstado(),
        ]);
    }

    // Eliminar periodo lectivo
    #[Route('/api/periodos-lectivos/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $periodoLectivo = $this->periodoLectivoService->getPeriodoLectivoById($id);
        if (!$periodoLectivo) {
            //Periodo lectivo no encontrado
            return $this->json(['error' => 'Periodo lectivo no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $this->periodoLectivoService->deletePeriodoLectivo($periodoLectivo);
        //Periodo lectivo eliminado correctamente
        return $this->json(['message' => 'Periodo lectivo eliminado correctamente']);
    }
}
