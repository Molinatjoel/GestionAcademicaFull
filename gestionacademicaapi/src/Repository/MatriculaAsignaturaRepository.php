<?php
namespace App\Repository;

use App\Entity\MatriculaAsignatura;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MatriculaAsignaturaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MatriculaAsignatura::class);
    }

    /**
     * @return MatriculaAsignatura[]
     */
    public function findByMatriculaId(int $matriculaId): array
    {
        return $this->createQueryBuilder('ma')
            ->andWhere('IDENTITY(ma.matricula) = :mid')
            ->setParameter('mid', $matriculaId)
            ->getQuery()
            ->getResult();
    }
}
