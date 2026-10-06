<?php

namespace App\Repository;

use App\Entity\Depense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Depense>
 */
class DepenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Depense::class);
    }

    public function totalEntre(\DateTimeImmutable $debut, \DateTimeImmutable $fin): int
    {
        return (int) $this->createQueryBuilder('d')
            ->select('COALESCE(SUM(d.montant), 0)')
            ->andWhere('d.dateDepense >= :d AND d.dateDepense < :f')
            ->setParameter('d', $debut)->setParameter('f', $fin)
            ->getQuery()->getSingleScalarResult();
    }

    /** @return \App\Entity\Depense[] */
    public function entre(\DateTimeImmutable $debut, \DateTimeImmutable $fin): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.dateDepense >= :d AND d.dateDepense < :f')
            ->setParameter('d', $debut)->setParameter('f', $fin)
            ->orderBy('d.dateDepense', 'DESC')->addOrderBy('d.id', 'DESC')
            ->getQuery()->getResult();
    }
}
