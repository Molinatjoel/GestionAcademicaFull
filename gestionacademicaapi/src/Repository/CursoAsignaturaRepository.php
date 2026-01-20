<?php
namespace App\Repository;

use App\Entity\CursoAsignatura;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CursoAsignaturaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CursoAsignatura::class);
    }
    // Métodos personalizados aquí
}
