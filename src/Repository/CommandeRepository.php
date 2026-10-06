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

    private function base(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->addSelect('cl', 'l', 'p')
            ->join('c.client', 'cl')
            ->leftJoin('c.lignes', 'l')
            ->leftJoin('c.paiements', 'p')
            ->orderBy('c.dateDepot', 'DESC');
    }

    /** @return Commande[] */
    public function rechercher(?string $q, ?string $statut, bool $retardSeulement, bool $impayesSeulement, ?Clients $client = null, int $limit = 300): array
    {
        $qb = $this->base()->setMaxResults($limit);
        if ($q) {
            $qb->andWhere('c.numero LIKE :q OR cl.nom LIKE :q OR cl.prenom LIKE :q OR cl.telephones LIKE :q')
                ->setParameter('q', '%'.$q.'%');
        }
        if ($statut && isset(Commande::STATUTS[$statut])) {
            $qb->andWhere('c.statut = :s')->setParameter('s', $statut);
        }
        if ($client) {
            $qb->andWhere('c.client = :client')->setParameter('client', $client);
        }
        if ($retardSeulement) {
            $qb->andWhere('c.statut IN (:encours)')->setParameter('encours', [Commande::STATUT_RECU, Commande::STATUT_EN_TRAITEMENT])
                ->andWhere('c.dateRetraitPrevue < :now')->setParameter('now', new \DateTimeImmutable());
        }

        /** @var Commande[] $res */
        $res = $qb->getQuery()->getResult();
        if ($impayesSeulement) {
            $res = array_values(array_filter($res, static fn (Commande $c) => Commande::STATUT_ANNULE !== $c->getStatut() && $c->getReste() > 0));
        }

        return $res;
    }

    /** @return Commande[] */
    public function enCours(): array
    {
        return $this->base()
            ->andWhere('c.statut IN (:s)')->setParameter('s', [Commande::STATUT_RECU, Commande::STATUT_EN_TRAITEMENT, Commande::STATUT_PRET])
            ->orderBy('c.dateRetraitPrevue', 'ASC')
            ->getQuery()->getResult();
    }

    /** Commandes non annulées avec un reste à payer. @return Commande[] */
    public function impayees(?Clients $client = null): array
    {
        $qb = $this->base()->andWhere('c.statut != :a')->setParameter('a', Commande::STATUT_ANNULE);
        if ($client) {
            $qb->andWhere('c.client = :client')->setParameter('client', $client);
        }

        return array_values(array_filter($qb->getQuery()->getResult(), static fn (Commande $c) => $c->getReste() > 0));
    }

    /** @return array<string,int> nombre de commandes par statut */
    public function compterParStatut(): array
    {
        $rows = $this->createQueryBuilder('c')->select('c.statut AS s, COUNT(c.id) AS n')->groupBy('c.statut')->getQuery()->getArrayResult();

        return array_column($rows, 'n', 's');
    }

    /** @return Commande[] */
    public function deposeesEntre(\DateTimeImmutable $debut, \DateTimeImmutable $fin): array
    {
        return $this->base()
            ->andWhere('c.dateDepot >= :d AND c.dateDepot < :f')
            ->andWhere('c.statut != :a')
            ->setParameter('d', $debut)->setParameter('f', $fin)->setParameter('a', Commande::STATUT_ANNULE)
            ->getQuery()->getResult();
    }

    /** @return array<int, array{client: Clients, total: int, nb: int}> */
    public function meilleursClients(\DateTimeImmutable $depuis, int $limit = 5): array
    {
        $par = [];
        foreach ($this->deposeesEntre($depuis, new \DateTimeImmutable('+1 day')) as $c) {
            $id = $c->getClient()->getId();
            $par[$id] ??= ['client' => $c->getClient(), 'total' => 0, 'nb' => 0];
            $par[$id]['total'] += $c->getTotal();
            ++$par[$id]['nb'];
        }
        usort($par, static fn ($a, $b) => $b['total'] <=> $a['total']);

        return \array_slice($par, 0, $limit);
    }
}
