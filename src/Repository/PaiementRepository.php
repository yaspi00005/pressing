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

    /** @param array{du?: ?\DateTimeImmutable, au?: ?\DateTimeImmutable, mode?: ?string, user?: ?int, q?: ?string} $f */
    public function filtre(array $f): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('c', 'cl', 'u')
            ->join('p.commande', 'c')->join('c.client', 'cl')->leftJoin('p.createdBy', 'u')
            ->orderBy('p.datePaiement', 'DESC')->addOrderBy('p.id', 'DESC');
        if (!empty($f['du'])) {
            $qb->andWhere('p.datePaiement >= :du')->setParameter('du', $f['du']->setTime(0, 0));
        }
        if (!empty($f['au'])) {
            $qb->andWhere('p.datePaiement < :au')->setParameter('au', $f['au']->setTime(0, 0)->modify('+1 day'));
        }
        if (!empty($f['mode']) && isset(\App\Entity\Paiement::MODES[$f['mode']])) {
            $qb->andWhere('p.mode = :m')->setParameter('m', $f['mode']);
        }
        if (!empty($f['user'])) {
            $qb->andWhere('p.createdBy = :u')->setParameter('u', $f['user']);
        }
        if (!empty($f['q'])) {
            $qb->andWhere('c.numero LIKE :q OR cl.nom LIKE :q OR cl.prenom LIKE :q OR p.reference LIKE :q')->setParameter('q', '%'.$f['q'].'%');
        }

        return $qb;
    }

    /** @return array{total: int, nb: int, parMode: array<string,int>, parCaissier: array<string,int>} */
    public function resume(array $f): array
    {
        $parMode = [];
        $parCaissier = [];
        $total = 0;
        $nb = 0;
        foreach ($this->filtre($f)->getQuery()->getResult() as $p) {
            $total += $p->getMontant();
            ++$nb;
            $parMode[$p->getMode()] = ($parMode[$p->getMode()] ?? 0) + $p->getMontant();
            $nom = $p->getCreatedBy()?->getNomComplet() ?? '—';
            $parCaissier[$nom] = ($parCaissier[$nom] ?? 0) + $p->getMontant();
        }
        arsort($parMode);
        arsort($parCaissier);

        return ['total' => $total, 'nb' => $nb, 'parMode' => $parMode, 'parCaissier' => $parCaissier];
    }
}
