<?php

namespace App\Repository;

use App\Entity\ArticlesSousCategorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticlesSousCategorie>
 *
 * @method ArticlesSousCategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticlesSousCategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticlesSousCategorie[]    findAll()
 * @method ArticlesSousCategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticlesSousCategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticlesSousCategorie::class);
    }

//    /**
//     * @return ArticlesSousCategorie[] Returns an array of ArticlesSousCategorie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ArticlesSousCategorie
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
