<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\ThingType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;


class CreativeWorkType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
        // ->add('headline')
        ->add('alternativeHeadline', TextType::class, array(
          'label' => 'title',
          // 'css_class' => 'not_compound',
          // 'attr' => array('css_class' => 'not_compound')
        ))
        ->add('text', CKEditorType::class)
        ->add('keyword')
        // ->add('dateCreated')
        // ->add('dateModified')
        // ->add('datePublished')
        // ->add('expire')
        ->add('thing', ThingType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\CreativeWork',
            'cascade_validation' => true,
            'allow_extra_fields' => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_creative_work';
    }


}
