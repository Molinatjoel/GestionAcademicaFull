<?php
namespace App\Controller;

use App\Service\DatosFamiliaresService;
use App\Request\DatosFamiliaresRequest;
use App\Entity\DatosFamiliares;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DatosFamiliaresController extends AbstractController
{
    private DatosFamiliaresService $datosFamiliaresService;
    private ValidatorInterface $validator;

    // Inyección del servicio DatosFamiliaresService y el validador
    public function __construct(DatosFamiliaresService $datosFamiliaresService, ValidatorInterface $validator)
    {
        $this->datosFamiliaresService = $datosFamiliaresService;
        $this->validator = $validator;
    }

    // Crear datos familiares
    #[Route('/api/datos-familiares', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dfRequest = new DatosFamiliaresRequest();
        $dfRequest->id_estudiante = $data['id_estudiante'] ?? null;
        $dfRequest->id_representante_user = $data['id_representante_user'] ?? null;
        $dfRequest->nombre_padre = $data['nombre_padre'] ?? null;
        $dfRequest->telefono_padre = $data['telefono_padre'] ?? null;
        $dfRequest->nombre_madre = $data['nombre_madre'] ?? null;
        $dfRequest->telefono_madre = $data['telefono_madre'] ?? null;
        $dfRequest->direccion_familiar = $data['direccion_familiar'] ?? null;
        $dfRequest->parentesco_representante = $data['parentesco_representante'] ?? null;
        $dfRequest->nombre_representante = $data['nombre_representante'] ?? null;
        $dfRequest->ocupacion_representante = $data['ocupacion_representante'] ?? null;
        $dfRequest->telefono_representante = $data['telefono_representante'] ?? null;
        $errors = $this->validator->validate($dfRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            //Errores de validación
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $datosFamiliares = $this->datosFamiliaresService->createDatosFamiliares($data);
        $estudiante = $datosFamiliares->getEstudiante();
        $representante = $datosFamiliares->getRepresentanteUser();
        return $this->json([
            'id_datos_familiares' => $datosFamiliares->getIdDatosFamiliares(),
            'id_estudiante' => $estudiante?->getId(),
            'estudiante' => $estudiante ? trim(($estudiante->getNombres() ?? '') . ' ' . ($estudiante->getApellidos() ?? '')) : null,
            'nombre_padre' => $datosFamiliares->getNombrePadre(),
            'telefono_padre' => $datosFamiliares->getTelefonoPadre(),
            'nombre_madre' => $datosFamiliares->getNombreMadre(),
            'telefono_madre' => $datosFamiliares->getTelefonoMadre(),
            'direccion_familiar' => $datosFamiliares->getDireccionFamiliar(),
            'parentesco_representante' => $datosFamiliares->getParentescoRepresentante(),
            'nombre_representante' => $datosFamiliares->getNombreRepresentante(),
            'ocupacion_representante' => $datosFamiliares->getOcupacionRepresentante(),
            'telefono_representante' => $datosFamiliares->getTelefonoRepresentante(),
            'id_representante_user' => $representante?->getId(),
            'representante_correo' => $representante?->getCorreo(),
        ]);
    }

    // Listar todos los datos familiares
    #[Route('/api/datos-familiares', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $datosFamiliares = $this->datosFamiliaresService->getAllDatosFamiliares();
        $payload = array_map(static function (DatosFamiliares $df) {
            $estudiante = $df->getEstudiante();
            $representante = $df->getRepresentanteUser();

            return [
                'id_datos_familiares' => $df->getIdDatosFamiliares(),
                'id_estudiante' => $estudiante?->getId(),
                'estudiante' => $estudiante ? trim(($estudiante->getNombres() ?? '') . ' ' . ($estudiante->getApellidos() ?? '')) : null,
                'nombre_padre' => $df->getNombrePadre(),
                'telefono_padre' => $df->getTelefonoPadre(),
                'nombre_madre' => $df->getNombreMadre(),
                'telefono_madre' => $df->getTelefonoMadre(),
                'direccion_familiar' => $df->getDireccionFamiliar(),
                'parentesco_representante' => $df->getParentescoRepresentante(),
                'nombre_representante' => $df->getNombreRepresentante(),
                'ocupacion_representante' => $df->getOcupacionRepresentante(),
                'telefono_representante' => $df->getTelefonoRepresentante(),
                'id_representante_user' => $representante?->getId(),
                'representante_correo' => $representante?->getCorreo(),
            ];
        }, $datosFamiliares);

        return $this->json($payload);
    }

    // Obtener datos familiares por ID
    #[Route('/api/datos-familiares/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $datosFamiliares = $this->datosFamiliaresService->getDatosFamiliaresById($id);
        if (!$datosFamiliares) {
            //Datos familiares no encontrados
            return $this->json(['error' => 'Datos familiares no encontrados'], Response::HTTP_NOT_FOUND);
        }
        $estudiante = $datosFamiliares->getEstudiante();
        $representante = $datosFamiliares->getRepresentanteUser();

        return $this->json([
            'id_datos_familiares' => $datosFamiliares->getIdDatosFamiliares(),
            'id_estudiante' => $estudiante?->getId(),
            'estudiante' => $estudiante ? trim(($estudiante->getNombres() ?? '') . ' ' . ($estudiante->getApellidos() ?? '')) : null,
            'nombre_padre' => $datosFamiliares->getNombrePadre(),
            'telefono_padre' => $datosFamiliares->getTelefonoPadre(),
            'nombre_madre' => $datosFamiliares->getNombreMadre(),
            'telefono_madre' => $datosFamiliares->getTelefonoMadre(),
            'direccion_familiar' => $datosFamiliares->getDireccionFamiliar(),
            'parentesco_representante' => $datosFamiliares->getParentescoRepresentante(),
            'nombre_representante' => $datosFamiliares->getNombreRepresentante(),
            'ocupacion_representante' => $datosFamiliares->getOcupacionRepresentante(),
            'telefono_representante' => $datosFamiliares->getTelefonoRepresentante(),
            'id_representante_user' => $representante?->getId(),
            'representante_correo' => $representante?->getCorreo(),
        ]);
    }

    // Actualizar datos familiares
    #[Route('/api/datos-familiares/{id}', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $datosFamiliares = $this->datosFamiliaresService->getDatosFamiliaresById($id);
        if (!$datosFamiliares) {
            //Datos familiares no encontrados
            return $this->json(['error' => 'Datos familiares no encontrados'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        $datosFamiliares = $this->datosFamiliaresService->updateDatosFamiliares($datosFamiliares, $data);
        $estudiante = $datosFamiliares->getEstudiante();
        $representante = $datosFamiliares->getRepresentanteUser();
        return $this->json([
            'id_datos_familiares' => $datosFamiliares->getIdDatosFamiliares(),
            'id_estudiante' => $estudiante?->getId(),
            'estudiante' => $estudiante ? trim(($estudiante->getNombres() ?? '') . ' ' . ($estudiante->getApellidos() ?? '')) : null,
            'nombre_padre' => $datosFamiliares->getNombrePadre(),
            'telefono_padre' => $datosFamiliares->getTelefonoPadre(),
            'nombre_madre' => $datosFamiliares->getNombreMadre(),
            'telefono_madre' => $datosFamiliares->getTelefonoMadre(),
            'direccion_familiar' => $datosFamiliares->getDireccionFamiliar(),
            'parentesco_representante' => $datosFamiliares->getParentescoRepresentante(),
            'nombre_representante' => $datosFamiliares->getNombreRepresentante(),
            'ocupacion_representante' => $datosFamiliares->getOcupacionRepresentante(),
            'telefono_representante' => $datosFamiliares->getTelefonoRepresentante(),
            'id_representante_user' => $representante?->getId(),
            'representante_correo' => $representante?->getCorreo(),
        ]);
    }

    // Eliminar datos familiares
    #[Route('/api/datos-familiares/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $datosFamiliares = $this->datosFamiliaresService->getDatosFamiliaresById($id);
        if (!$datosFamiliares) {
            //Datos familiares no encontrados
            return $this->json(['error' => 'Datos familiares no encontrados'], Response::HTTP_NOT_FOUND);
        }
        $this->datosFamiliaresService->deleteDatosFamiliares($datosFamiliares);
        //Datos familiares eliminados correctamente
        return $this->json(['message' => 'Datos familiares eliminados correctamente']);
    }
}
