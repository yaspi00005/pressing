<?php

namespace App\Repository;

use App\Entity\Journal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Journal>
 */
class JournalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Journal::class);
    }

    /** @return Journal[] */
    public function pourCible(string $cible, int $id, int $limit = 30): array
    {
        return $this->createQueryBuilder('j')
            ->addSelect('u')->leftJoin('j.user', 'u')
            ->andWhere('j.cible = :c AND j.cibleId = :id')
            ->setParameter('c', $cible)->setParameter('id', $id)
            ->orderBy('j.createdAt', 'DESC')->addOrderBy('j.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    public function filtre(?string $q, ?string $cible, ?int $userId): QueryBuilder
    {
        $qb = $this->createQueryBuilder('j')->addSelect('u')->leftJoin('j.user', 'u')->orderBy('j.createdAt', 'DESC')->addOrderBy('j.id', 'DESC');
        if ($q) {
            $qb->andWhere('j.libelle LIKE :q')->setParameter('q', '%'.$q.'%');
        }
        if ($cible) {
            $qb->andWhere('j.cible = :c')->setParameter('c', $cible);
        }
        if ($userId) {
            $qb->andWhere('j.user = :u')->setParameter('u', $userId);
        }

        return $qb;
    }
}
