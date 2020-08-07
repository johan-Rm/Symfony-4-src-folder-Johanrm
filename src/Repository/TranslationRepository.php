<?php

namespace App\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use App\Entity\AccommodationNature;


class TranslationRepository extends EntityRepository
{
    public function findWithoutSlug($lang)
    {
        return $this->createQueryBuilder('t')
            ->where('t.fieldName != :field_name')
            ->setParameter('field_name', "slug")
            ->andWhere('t.lang = :lang')
            ->setParameter('lang', $lang)
            // ->orderBy('u.id', 'ASC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findWithSlug($lang)
    {
        return $this->createQueryBuilder('t')
            ->where('t.fieldName = :field_name')
            ->setParameter('field_name', "slug")
            ->andWhere('t.lang = :lang')
            ->setParameter('lang', $lang)
            // ->orderBy('u.id', 'ASC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

}
