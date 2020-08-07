<?php

namespace App\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;


class PaymentMethodRepository extends EntityRepository
{
    public static function getPaymentMethod(EntityRepository $er, EntityManager $em, TokenStorageInterface $tokenStorage, TranslatorInterface $translator)
    {
        $query = $er->createQueryBuilder('payment_method');

        return $query;
    }
}
