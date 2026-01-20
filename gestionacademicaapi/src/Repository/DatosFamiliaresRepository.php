<?php
namespace App\Repository;

use App\Entity\DatosFamiliares;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DatosFamiliaresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DatosFamiliares::class);
    }
    // Métodos personalizados aquí
}
