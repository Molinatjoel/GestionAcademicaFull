<?php
namespace App\Service;

use App\Entity\DatosFamiliares;
use App\Interface\DatosFamiliaresServiceInterface;
use App\Repository\DatosFamiliaresRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class DatosFamiliaresService implements DatosFamiliaresServiceInterface
{
    private DatosFamiliaresRepository $datosFamiliaresRepository;
    private EntityManagerInterface $em;
    private UserRepository $userRepository;

    public function __construct(
        DatosFamiliaresRepository $datosFamiliaresRepository, 
        EntityManagerInterface $em,
        UserRepository $userRepository
    ) {
        $this->datosFamiliaresRepository = $datosFamiliaresRepository;
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    public function createDatosFamiliares(array $data): DatosFamiliares
    {
        $datosFamiliares = new DatosFamiliares();
        
        // Asignar estudiante
        if (isset($data['id_estudiante'])) {
            $estudiante = $this->userRepository->find($data['id_estudiante']);
            if ($estudiante) {
                $datosFamiliares->setEstudiante($estudiante);
            }
        }

        if (isset($data['id_representante_user'])) {
            $representante = $this->userRepository->find($data['id_representante_user']);
            $datosFamiliares->setRepresentanteUser($representante);
        }
        
        $datosFamiliares->setNombrePadre($data['nombre_padre'] ?? '');
        $datosFamiliares->setTelefonoPadre($data['telefono_padre'] ?? null);
        $datosFamiliares->setNombreMadre($data['nombre_madre'] ?? '');
        $datosFamiliares->setTelefonoMadre($data['telefono_madre'] ?? null);
        $datosFamiliares->setDireccionFamiliar($data['direccion_familiar'] ?? null);
        $datosFamiliares->setParentescoRepresentante($data['parentesco_representante'] ?? null);
        $datosFamiliares->setNombreRepresentante($data['nombre_representante'] ?? null);
        $datosFamiliares->setOcupacionRepresentante($data['ocupacion_representante'] ?? null);
        $datosFamiliares->setTelefonoRepresentante($data['telefono_representante'] ?? null);
        
        $this->em->persist($datosFamiliares);
        $this->em->flush();
        return $datosFamiliares;
    }

    public function updateDatosFamiliares(DatosFamiliares $datosFamiliares, array $data): DatosFamiliares
    {
        if (isset($data['id_estudiante'])) {
            $estudiante = $this->userRepository->find($data['id_estudiante']);
            if ($estudiante) {
                $datosFamiliares->setEstudiante($estudiante);
            }
        }

        if (array_key_exists('id_representante_user', $data)) {
            $representante = $data['id_representante_user'] ? $this->userRepository->find($data['id_representante_user']) : null;
            $datosFamiliares->setRepresentanteUser($representante);
        }
        
        if (isset($data['nombre_padre'])) {
            $datosFamiliares->setNombrePadre($data['nombre_padre']);
        }
        if (isset($data['telefono_padre'])) {
            $datosFamiliares->setTelefonoPadre($data['telefono_padre']);
        }
        if (isset($data['nombre_madre'])) {
            $datosFamiliares->setNombreMadre($data['nombre_madre']);
        }
        if (isset($data['telefono_madre'])) {
            $datosFamiliares->setTelefonoMadre($data['telefono_madre']);
        }
        if (isset($data['direccion_familiar'])) {
            $datosFamiliares->setDireccionFamiliar($data['direccion_familiar']);
        }
        if (isset($data['parentesco_representante'])) {
            $datosFamiliares->setParentescoRepresentante($data['parentesco_representante']);
        }
        if (isset($data['nombre_representante'])) {
            $datosFamiliares->setNombreRepresentante($data['nombre_representante']);
        }
        if (isset($data['ocupacion_representante'])) {
            $datosFamiliares->setOcupacionRepresentante($data['ocupacion_representante']);
        }
        if (isset($data['telefono_representante'])) {
            $datosFamiliares->setTelefonoRepresentante($data['telefono_representante']);
        }
        
        $this->em->flush();
        return $datosFamiliares;
    }

    public function deleteDatosFamiliares(DatosFamiliares $datosFamiliares): void
    {
        $this->em->remove($datosFamiliares);
        $this->em->flush();
    }

    public function getDatosFamiliaresById(int $id): ?DatosFamiliares
    {
        return $this->datosFamiliaresRepository->find($id);
    }

    public function getAllDatosFamiliares(): array
    {
        return $this->datosFamiliaresRepository->findAll();
    }
}
