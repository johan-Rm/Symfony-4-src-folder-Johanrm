<?php

namespace App\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use App\Entity\Event;


class RentalController extends AbstractController
{
    /**
     * The method that is executed when the user performs a 'list' action on an entity.
     *
     * @return Response
     */
    protected function listRentalAction()
    {
        if( null !== $this->request->query->get('type') 
          && 'full_calendar' == $this->request->query->get('type')
        ) {
            $this->entity['templates']['list'] = 'pages/appointment.html.twig';
        }

        return parent::listAction();
    }


 	  protected function createFiltersForm(string $entityName): FormInterface
    {
        $form = parent::createFiltersForm($entityName);
        $form->add('beginAt', DateType::class, array(
              'widget' => 'single_text',
              'format' => 'dd/MM/yyyy HH:mm',
              'attr' => array(
                  'class' => 'datetimepicker'
              )
            )
        );
        $form->add('endAt', DateType::class, array(
              'widget' => 'single_text',
              'format' => 'dd/MM/yyyy HH:mm',
              'attr' => array(
                  'class' => 'datetimepicker'
              )
            )
        );

        return $form;
    }

}
