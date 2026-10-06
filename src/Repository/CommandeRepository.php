<?php

namespace App\Repository;

use App\Entity\Clients;
use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function compterNumerosAvecPrefixe(string $prefix): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.numero LIKE :p')
            ->setParameter('p', $prefix.'%')
            ->getQuery()->getSingleScalarResult();
    }

    /**
     * Liste filtrée (les filtres viennent de l'adresse de la page).
     *
     * @param array{q?: ?string, statut?: ?string, filtre?: ?string, du?: ?\DateTimeImmutable, au?: ?\DateTimeImmutable, client?: ?Clients, livraison?: ?string} $f
     */
    public function filtre(array $f): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->addSelect('cl', 'l')
            ->join('c.client', 'cl')
            ->leftJoin('c.lignes', 'l')
            ->orderBy('c.dateDepot', 'DESC')->addOrderBy('c.id', 'DESC');

        return $this->appliquer($qb, $f);
    }

    /** @return array{nb: int, total: int, paye: int, reste: int} chiffres de la sélection (hors commandes annulées pour les montants) */
    public function totaux(array $f): array
    {
        $qb = $this->createQueryBuilder('c')->select('COUNT(c.id) AS nb')->join('c.client', 'cl');
        $this->appliquer($qb, $f);
        $nb = (int) $qb->getQuery()->getSingleScalarResult();

        $qb = $this->createQueryBuilder('c')
            ->select('COALESCE(SUM(c.total), 0) AS total, COALESCE(SUM(c.montantPaye), 0) AS paye')
            ->join('c.client', 'cl')
            ->andWhere('c.statut != :annule')->setParameter('annule', Commande::STATUT_ANNULE);
        $this->appliquer($qb, $f);
        $r = $qb->getQuery()->getSingleResult();

        return ['nb' => $nb, 'total' => (int) $r['total'], 'paye' => (int) $r['paye'], 'reste' => max(0, (int) $r['total'] - (int) $r['paye'])];
    }

    private function appliquer(QueryBuilder $qb, array $f): QueryBuilder
    {
        if (!empty($f['q'])) {
            $qb->andWhere('c.numero LIKE :q OR cl.nom LIKE :q OR cl.prenom LIKE :q OR cl.telephones LIKE :q')
                ->setParameter('q', '%'.$f['q'].'%');
        }
        if (!empty($f['statut']) && isset(Commande::STATUTS[$f['statut']])) {
            $qb->andWhere('c.statut = :s')->setParameter('s', $f['statut']);
        }
        if (!empty($f['client'])) {
            $qb->andWhere('c.client = :client')->setParameter('client', $f['client']);
        }
        if (!empty($f['du'])) {
            $qb->andWhere('c.dateDepot >= :du')->setParameter('du', $f['du']->setTime(0, 0));
        }
        if (!empty($f['au'])) {
            $qb->andWhere('c.dateDepot < :au')->setParameter('au', $f['au']->setTime(0, 0)->modify('+1 day'));
        }
        if (!empty($f['livraison']) && \in_array($f['livraison'], [Commande::MODE_RETRAIT, Commande::MODE_DOMICILE], true)) {
            $qb->andWhere('c.modeLivraison = :ml')->setParameter('ml', $f['livraison']);
        }
        switch ($f['filtre'] ?? null) {
            case 'retard':
                $qb->andWhere('c.statut IN (:encours)')->setParameter('encours', [Commande::STATUT_RECU, Commande::STATUT_EN_TRAITEMENT])
                    ->andWhere('c.dateRetraitPrevue < :now')->setParameter('now', new \DateTimeImmutable());
                break;
            case 'impayes':
                $qb->andWhere('c.statut != :annule_i')->setParameter('annule_i', Commande::STATUT_ANNULE)
                    ->andWhere('c.total > c.montantPaye');
                break;
            case 'urgent':
                $qb->andWhere('c.urgent = true');
                break;
            case 'encours':
                $qb->andWhere('c.statut IN (:ec)')->setParameter('ec', [Commande::STATUT_RECU, Commande::STATUT_EN_TRAITEMENT, Commande::STATUT_PRET]);
                break;
        }

        return $qb;
    }

    /** @return Commande[] */
    public function rechercher(?string $q, ?string $statut, bool $retardSeulement, bool $impayesSeulement, ?Clients $client = null, int $limit = 300): array
    {
        $filtre = $retardSeulement ? 'retard' : ($impayesSeulement ? 'impayes' : null);

        return $this->filtre(['q' => $q, 'statut' => $statut, 'filtre' => $filtre, 'client' => $client])->setMaxResults($limit)->getQuery()->getResult();
    }

    /** Commandes à traiter, les plus urgentes d'abord. @return Commande[] */
    public function enCours(int $limit = 50): array
    {
        return $this->filtre(['filtre' => 'encours'])
            ->orderBy('c.dateRetraitPrevue', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    /** @return array<string,int> nombre de commandes par statut */
    public function compterParStatut(): array
    {
        $rows = $this->createQueryBuilder('c')->select('c.statut AS s, COUNT(c.id) AS n')->groupBy('c.statut')->getQuery()->getArrayResult();

        return array_map('intval', array_column($rows, 'n', 's'));
    }

    public function compterRetards(): int
    {
        return (int) $this->createQueryBuilder('c')->select('COUNT(c.id)')
            ->andWhere('c.statut IN (:s)')->setParameter('s', [Commande::STATUT_RECU, Commande::STATUT_EN_TRAITEMENT])
            ->andWhere('c.dateRetraitPrevue < :now')->setParameter('now', new \DateTimeImmutable())
            ->getQuery()->getSingleScalarResult();
    }

    /** @return array{nb: int, montant: int} commandes non annulées avec un reste à payer */
    public function impayes(): array
    {
        $r = $this->createQueryBuilder('c')
            ->select('COUNT(c.id) AS nb, COALESCE(SUM(c.total - c.montantPaye), 0) AS montant')
            ->andWhere('c.statut != :a')->setParameter('a', Commande::STATUT_ANNULE)
            ->andWhere('c.total > c.montantPaye')
            ->getQuery()->getSingleResult();

        return ['nb' => (int) $r['nb'], 'montant' => (int) $r['montant']];
    }

    /** Chiffre d'affaires (commandes non annulées) déposées dans la période. */
    public function chiffreAffaires(\DateTimeImmutable $debut, \DateTimeImmutable $fin): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COALESCE(SUM(c.total), 0)')
            ->andWhere('c.dateDepot >= :d AND c.dateDepot < :f')->andWhere('c.statut != :a')
            ->setParameter('d', $debut)->setParameter('f', $fin)->setParameter('a', Commande::STATUT_ANNULE)
            ->getQuery()->getSingleScalarResult();
    }

    public function compterDeposees(\DateTimeImmutable $debut, \DateTimeImmutable $fin): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.dateDepot >= :d AND c.dateDepot < :f')->andWhere('c.statut != :a')
            ->setParameter('d', $debut)->setParameter('f', $fin)->setParameter('a', Commande::STATUT_ANNULE)
            ->getQuery()->getSingleScalarResult();
    }

    /** @return array<string,array{nb:int,total:int}> par mois (Y-m) sur la période */
    public function parMois(\DateTimeImmutable $debut, \DateTimeImmutable $fin): array
    {
        $par = [];
        foreach ($this->createQueryBuilder('c')->select('c.dateDepot, c.total')
            ->andWhere('c.dateDepot >= :d AND c.dateDepot < :f')->andWhere('c.statut != :a')
            ->setParameter('d', $debut)->setParameter('f', $fin)->setParameter('a', Commande::STATUT_ANNULE)
            ->getQuery()->getArrayResult() as $r) {
            $k = $r['dateDepot']->format('Y-m');
            $par[$k] ??= ['nb' => 0, 'total' => 0];
            ++$par[$k]['nb'];
            $par[$k]['total'] += $r['total'];
        }

        return $par;
    }

    /** @return array<int, array{client: Clients, total: int, nb: int}> */
    public function meilleursClients(\DateTimeImmutable $depuis, int $limit = 5): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select('IDENTITY(c.client) AS cid, COUNT(c.id) AS nb, SUM(c.total) AS total')
            ->andWhere('c.dateDepot >= :d')->andWhere('c.statut != :a')
            ->setParameter('d', $depuis)->setParameter('a', Commande::STATUT_ANNULE)
            ->groupBy('c.client')->orderBy('total', 'DESC')->setMaxResults($limit)
            ->getQuery()->getArrayResult();

        $em = $this->getEntityManager();

        return array_map(static fn ($r) => ['client' => $em->getReference(Clients::class, $r['cid']), 'nb' => (int) $r['nb'], 'total' => (int) $r['total']], $rows);
    }
}
