<?php
namespace App\Controller;

use App\Service\ReporteService;
use App\Request\ReporteRequest;
use App\Entity\Reporte;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReporteController extends AbstractController
{
    private ReporteService $reporteService;
    private ValidatorInterface $validator;

    // Inyección del servicio ReporteService y el validador
    public function __construct(ReporteService $reporteService, ValidatorInterface $validator)
    {
        $this->reporteService = $reporteService;
        $this->validator = $validator;
    }

    // Crear reporte
    #[Route('/api/reportes', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $reporteRequest = new ReporteRequest();
        $reporteRequest->titulo = $data['titulo'] ?? '';
        $reporteRequest->descripcion = $data['descripcion'] ?? '';
        $reporteRequest->tipo = $data['tipo'] ?? '';
        $reporteRequest->id_curso = $data['id_curso'] ?? null;
        $reporteRequest->id_docente = $data['id_docente'] ?? null;
        $reporteRequest->id_periodo = $data['id_periodo'] ?? null;
        $reporteRequest->fecha_creacion = new \DateTime();
        $errors = $this->validator->validate($reporteRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            //Errores de validación
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $reporte = $this->reporteService->createReporte($data);
        return $this->json($reporte);
    }

    // Listar todos los reportes
    #[Route('/api/reportes', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $reportes = $this->reporteService->getAllReportes();
        return $this->json($reportes);
    }

    // Obtener reporte por ID
    #[Route('/api/reportes/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $reporte = $this->reporteService->getReporteById($id);
        if (!$reporte) {
            //Reporte no encontrado
            return $this->json(['error' => 'Reporte no encontrado'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($reporte);
    }

    // Actualizar reporte
    #[Route('/api/reportes/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $reporte = $this->reporteService->getReporteById($id);
        if (!$reporte) {
            //Reporte no encontrado
            return $this->json(['error' => 'Reporte no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        $reporte = $this->reporteService->updateReporte($reporte, $data);
        return $this->json($reporte);
    }

    // Eliminar reporte
    #[Route('/api/reportes/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $reporte = $this->reporteService->getReporteById($id);
        if (!$reporte) {
            //Reporte no encontrado
            return $this->json(['error' => 'Reporte no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $this->reporteService->deleteReporte($reporte);
        //Reporte eliminado correctamente
        return $this->json(['message' => 'Reporte eliminado correctamente']);
    }
}
