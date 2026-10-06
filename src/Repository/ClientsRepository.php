<?php

namespace App\Repository;

use App\Entity\Clients;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Clients>
 *
 * @method Clients|null find($id, $lockMode = null, $lockVersion = null)
 * @method Clients|null findOneBy(array $criteria, array $orderBy = null)
 * @method Clients[]    findAll()
 * @method Clients[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Clients::class);
    }

//    /**
//     * @return Clients[] Returns an array of Clients objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Clients
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function filtre(?string $q, ?string $genre): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')->orderBy('c.nom', 'ASC')->addOrderBy('c.prenom', 'ASC');
        if ($q) {
            $qb->andWhere('c.nom LIKE :q OR c.prenom LIKE :q OR c.telephones LIKE :q OR c.email LIKE :q OR c.adresses LIKE :q')->setParameter('q', '%'.$q.'%');
        }
        if ($genre) {
            $qb->andWhere('c.genres = :g')->setParameter('g', $genre);
        }

        return $qb;
    }

    /**
     * Nombre de commandes et solde dû par client.
     *
     * @param int[] $ids
     *
     * @return array<int, array{nb: int, solde: int}>
     */
    public function statistiques(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $rows = $this->getEntityManager()->createQueryBuilder()
            ->select('IDENTITY(c.client) AS cid, COUNT(c.id) AS nb, COALESCE(SUM(CASE WHEN c.statut != :a THEN c.total - c.montantPaye ELSE 0 END), 0) AS solde')
            ->from(\App\Entity\Commande::class, 'c')
            ->andWhere('c.client IN (:ids)')->setParameter('ids', $ids)->setParameter('a', \App\Entity\Commande::STATUT_ANNULE)
            ->groupBy('c.client')->getQuery()->getArrayResult();

        $par = [];
        foreach ($rows as $r) {
            $par[(int) $r['cid']] = ['nb' => (int) $r['nb'], 'solde' => (int) $r['solde']];
        }

        return $par;
    }
}
