<?php

namespace App\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use App\Entity\MediaObject;


class MediaObjectRepository extends EntityRepository
{
    public static function getVideos(EntityRepository $er, EntityManager $em, TokenStorageInterface $tokenStorage, TranslatorInterface $translator)
    {
        // $mediaObjectRepository = $em->getRepository(MediaObject::class);
        $encoding_format = 'video/youtube';
        $query = $er->createQueryBuilder('media')
             // ->leftJoin('person.userCreated', 'user')
          ->andWhere('media.encodingFormat = :encoding_format')
          ->setParameter('encoding_format', $encoding_format)
        ;

        return $query;
    }

   public function findByDate($dateBegin = null, $dateEnd = null)
    {
        $query = $this->createQueryBuilder('media')
            ->andWhere('DATE(media.dateCreated) BETWEEN :dateBegin AND :dateEnd')
            ->setParameter('dateEnd', $dateEnd)
            ->setParameter('dateBegin', $dateBegin)
            ->andWhere('media.isActive = 1')
        ;

        return $query->getQuery()->getResult();
    }

}
