<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;


class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type')
            ->add('name', null, [
                    'help' => 'The file name will automatically be saved by default',
                ]
            )
            ->add('description')
            ->add('file', VichFileType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
         $resolver->setDefaults(array(
            'data_class' => 'App\Entity\DocumentObject',
            'allow_extra_fields' => true
        ));
    }
}
