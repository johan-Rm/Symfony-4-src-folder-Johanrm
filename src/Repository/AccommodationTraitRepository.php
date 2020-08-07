<?php

namespace App\Repository;

use App\Entity\AccommodationTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AccommodationTrait|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccommodationTrait|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccommodationTrait[]    findAll()
 * @method AccommodationTrait[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccommodationTraitRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AccommodationTrait::class);
    }

    // /**
    //  * @return AccommodationTrait[] Returns an array of AccommodationTrait objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AccommodationTrait
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
