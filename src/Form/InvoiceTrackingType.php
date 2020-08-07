<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
// use App\Form\Extension\EntityTypeExtension;

class InvoiceTrackingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name')
          ->add('invoiceDate', null, array(
              'widget' => 'single_text',
              'format' => 'dd/MM/yyyy HH:mm',
              'attr' => array(
                  'class' => 'datetimepicker'
              )
            )
            )
          ->add('paymentDate', null, array(
              'widget' => 'single_text',
              'format' => 'dd/MM/yyyy HH:mm',
              'attr' => array(
                  'class' => 'datetimepicker'
              )
            )
          )
          ->add('paymentMethod')
          ->add('amount')
          ->add('comment')
          ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\InvoiceTracking',
            'allow_extra_fields' => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_invoicetracking';
    }
}
