<?php

namespace App\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use App\Entity\AccommodationNature;


class AccommodationNatureRepository extends EntityRepository
{
    public static function getLocation(EntityRepository $er, EntityManager $em, TokenStorageInterface $tokenStorage, TranslatorInterface $translator)
    {
        $criteria = array(
            'slug' => 'location'
        );
        // $user = $tokenStorage->getToken()->getUser();

          $accommodationNatureRepository = $em->getRepository(AccommodationNature::class);
          $parent = $accommodationNatureRepository->findBy(array('slug' => 'location'));
          // dump($parent);die;
            $query = $er->createQueryBuilder('accommodationNature')
               // ->leftJoin('person.userCreated', 'user')
               ->andWhere('accommodationNature.parent = :parent')
               ->setParameter('parent', $parent)
            ;

        return $query;
    }

    public static function getMain(EntityRepository $er, EntityManager $em, TokenStorageInterface $tokenStorage, TranslatorInterface $translator)
    {
        $query = $er->createQueryBuilder('accommodationNature')
           ->andWhere('accommodationNature.parent IS NULL')
           // ->setParameter('parent', null)
        ;

        return $query;
    }


}
