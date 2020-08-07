<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


class VideoObjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', null, [
                    'help' => 'Enter the youtube url of your video',
                ]
            )
            ->add('encoding_format', HiddenType::class, array(
                    'empty_data' => 'video/youtube'
                )
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
         $resolver->setDefaults(array(
            'data_class' => 'App\Entity\MediaObject',
            'allow_extra_fields' => true
        ));
    }
}
