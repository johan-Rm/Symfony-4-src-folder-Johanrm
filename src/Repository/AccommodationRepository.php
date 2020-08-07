<?php

namespace App\Repository;

use App\Entity\Accommodation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Accommodation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Accommodation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Accommodation[]    findAll()
 * @method Accommodation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccommodationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Accommodation::class);
    }

    public function getColumnsForCsv($class)
    {
        $columns[$class] = [
            'reference' => function (Accommodation $accommodation) {
                return $accommodation->getReference();
            },
            'price' => function (Accommodation $accommodation) {
                return $accommodation->getPrice();
            }
        ];

        return $columns[$class];
    }

    public function findByRentingCriteria($criteria)
    {
       
        return $this->createQueryBuilder('a')
            ->andWhere('a.nature = :nature')
            ->setParameter('nature', $criteria['nature'])
            ->andWhere('a.duration = :duration')
            ->setParameter('duration', $criteria['duration'])
            ->andWhere('a.reference IS NOT NULL')
            // ->orderBy('a.id', 'ASC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findBySellingCriteria($criteria)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nature = :nature')
            ->setParameter('nature', $criteria['nature'])
            ->andWhere('a.type = :type')
            ->setParameter('type', $criteria['type'])
            ->andWhere('a.reference IS NOT NULL')
            // ->orderBy('a.id', 'ASC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Accommodation
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
