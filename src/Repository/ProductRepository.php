<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository class for managing Product entities.
 *
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    /**
     * Constructor for the ProductRepository class.
     *
     * @param ManagerRegistry $registry The registry service.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Find all products having a value above the specified one.
     *
     * @param int $value The minimum value to filter products.
     *
     * @return Product[] Returns an array of Product objects.
     */
    public function findByMinimumValue($value): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.value >= :value')
            ->setParameter('value', $value)
            ->orderBy('p.value', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find all products having a value above the specified one using raw SQL.
     *
     * @param int $value The minimum value to filter products.
     *
     * @return array[] Returns an array of arrays (i.e. a raw data set).
     */
    public function findByMinimumValue2($value): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM product AS p
            WHERE p.value >= :value
            ORDER BY p.value ASC
        ';

        $resultSet = $conn->executeQuery($sql, ['value' => $value]);

        return $resultSet->fetchAllAssociative();
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
