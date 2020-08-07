<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateSent')
            ->add('name')
            ->add('description')
            // ->add('url')
            // ->add('mainEntityOfPage')
            // ->add('headline')
            // ->add('alternativeHeadline')
            // ->add('text')
            // ->add('keywords')
            // ->add('datePublished')
            // ->add('expire')
            // ->add('dateCreated')
            // ->add('dateModified')
            // ->add('recipient')
            // ->add('sender')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
            'form_name' => 'MessageType',
        ]);
    }
}
