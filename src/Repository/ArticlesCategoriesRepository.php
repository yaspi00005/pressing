<?php

namespace App\Repository;

use App\Entity\ArticlesCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticlesCategories>
 *
 * @method ArticlesCategories|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticlesCategories|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticlesCategories[]    findAll()
 * @method ArticlesCategories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticlesCategoriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticlesCategories::class);
    }

//    /**
//     * @return ArticlesCategories[] Returns an array of ArticlesCategories objects
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

//    public function findOneBySomeField($value): ?ArticlesCategories
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
