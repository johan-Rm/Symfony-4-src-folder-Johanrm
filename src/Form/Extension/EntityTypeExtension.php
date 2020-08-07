<?php

namespace App\Form\Extension;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class EntityTypeExtension extends AbstractTypeExtension
{
    private $tokenStorage;
    private $translator;

    public function __construct(TokenStorageInterface $tokenStorage, TranslatorInterface $translator, EntityManager $em)
    {
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
              'query_builder_options' => array('disabled' => true), // <-- custom option for this feature.
        ));

        $normalizer = function (Options $options, $queryBuilder) {

            if (is_callable($queryBuilder)) {
                $queryBuilder = call_user_func(
                    $queryBuilder,
                    $options['em']->getRepository($options['class']),
                    $this->em,
                    $this->tokenStorage,
                    $this->translator,
                   $options // <-- type_options,

                );
            }

            return $queryBuilder;
        };

        $resolver->setNormalizer('query_builder', $normalizer);
    }

    public function getExtendedType()
    {
        return EntityType::class;
    }
}
