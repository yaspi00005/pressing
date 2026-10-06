<?php

namespace App\Repository;

use App\Entity\Paiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Paiement>
 */
class PaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paiement::class);
    }

    public function totalEntre(\DateTimeImmutable $debut, \DateTimeImmutable $fin): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COALESCE(SUM(p.montant), 0)')
            ->andWhere('p.datePaiement >= :d AND p.datePaiement < :f')
            ->setParameter('d', $debut)->setParameter('f', $fin)
            ->getQuery()->getSingleScalarResult();
    }

    /** @return array<string,int> encaissements par mode de paiement */
    public function totalParMode(\DateTimeImmutable $debut, \DateTimeImmutable $fin): array
    {
        $rows = $this->createQueryBuilder('p')
            ->select('p.mode AS m, SUM(p.montant) AS t')
            ->andWhere('p.datePaiement >= :d AND p.datePaiement < :f')
            ->setParameter('d', $debut)->setParameter('f', $fin)
            ->groupBy('p.mode')
            ->getQuery()->getArrayResult();

        return array_map('intval', array_column($rows, 't', 'm'));
    }

    /** @return \App\Entity\Paiement[] */
    public function entre(\DateTimeImmutable $debut, \DateTimeImmutable $fin): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('c', 'cl')
            ->join('p.commande', 'c')->join('c.client', 'cl')
            ->andWhere('p.datePaiement >= :d AND p.datePaiement < :f')
            ->setParameter('d', $debut)->setParameter('f', $fin)
            ->orderBy('p.datePaiement', 'ASC')
            ->getQuery()->getResult();
    }

    /** @return array<string,int> encaissements par jour (Y-m-d) */
    public function totalParJour(\DateTimeImmutable $debut, \DateTimeImmutable $fin): array
    {
        $par = [];
        foreach ($this->entre($debut, $fin) as $p) {
            $k = $p->getDatePaiement()->format('Y-m-d');
            $par[$k] = ($par[$k] ?? 0) + $p->getMontant();
        }

        return $par;
    }
}
