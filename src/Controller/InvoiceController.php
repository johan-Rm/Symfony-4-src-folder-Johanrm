<?php

namespace App\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use App\Entity\Invoice;
use App\Entity\InvoiceElement;
use App\Entity\Organization;


class InvoiceController extends AbstractController
{
    /**
     * @ Security("has_role('ROLE_ADMIN')")
     */
    protected function printAction()
    {
        $id = $this->request->query->get('id');
// dump('ici');die();
        $tpl = 'components/invoice.html.twig';

        $invoice = $this->em->getRepository(Invoice::class)->findOneBy(['id' => $id]);
        $invoiceElement = $this->em->getRepository(InvoiceElement::class)->findBy(['invoice' => $invoice]);

        $this->get('app.billing_calculator')->totalPaymentDue($invoice);

        $user = $invoice->getUserCreated();
        // $company = $this->em->getRepository(Organization::class)->findOneBy(['id' => 1]);
        $informations = array(
            'name' => 'My site name',
            'address' =>  array(
                'name' => 'The name of the address',
                'address' => 'My custom address',
                'postcode' => 'xxx-xxx',
                'city' => 'My city',
                'country' => 'My country'
            )
        );

        return $this->render($tpl, array(
                'invoice' => $invoice,
                'invoiceElement' => $invoiceElement,
                'informations' => $informations,
                'tva' => Invoice::TVA
            )
        );die;

// dump($company);die();
        $html = $this->renderView($tpl, array(
                'invoice' => $invoice,
                'invoiceElement' => $invoiceElement,
                'informations' => $informations,
                 'tva' => Invoice::TVA
            )
        );

       $name = 'file_test_' . date("d_m_Y_h_i_s") . '.pdf';
       $this->get('knp_snappy.pdf')->generateFromHtml($html, 'bundles/generated/' . $name);

       return new PdfResponse(
           $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
           $name
       );

    }

    /**
     * @ Security("has_role('ROLE_ADMIN')")
     */
    protected function viewAction()
    {
        // controllers extending the base AdminController get access to the
        // following variables:
        //   $this->request, stores the current request
        //   $this->em, stores the Entity Manager for this Doctrine entity

        // change the properties of the given entity and save the changes
        $id = $this->request->query->get('id');
        // $entity = $this->em->getRepository('App:Product')->find($id);
        // $entity->setStock(100 + $entity->getStock());
        // $this->em->flush();
        //
        // // redirect to the 'list' view of the given entity
        // return $this->redirectToRoute('easyadmin', array(
        //     'action' => 'list',
        //     'entity' => $this->request->query->get('entity'),
        // ));

        // redirect to the 'edit' view of the given entity item
        return $this->redirectToRoute('easyadmin', array(
            'action' => 'show',
            'id' => $id,
            'entity' => $this->request->query->get('entity'),
        ));
    }

    /**
     * The method that is executed when the user performs a 'new' action on an entity.
     *
     * @return Response|RedirectResponse
     */
    // protected function newInvoiceAction()
    // {
        // $this->dispatch(EasyAdminEvents::PRE_NEW);

        // $entity = $this->executeDynamicMethod('createNew<EntityName>Entity');
        // $fields = $this->entity['new']['fields'];
        // $entity = $this->setAdministrable($entity);
        // $fields = $this->setFields($entity, $fields);
        // $entity->setDate(new \DateTime());

        // return $this->newForm($entity, $fields);
        // return parent::newAction();
    // }

    /**
     * Allows applications to modify the entity associated with the item being
     * created while persisting it.
     *
     * @param object $entity
     */
    protected function persistInvoiceEntity($entity)
    {
        $this->get('app.billing_numerator')->incrementNumber($entity);
        $this->get('app.billing_calculator')->totalWithoutTaxes($entity);
        $this->em->persist($entity);
        $this->em->flush();
    }

    /**
     * Allows applications to modify the entity associated with the item being
     * created while persisting it.
     *
     * @param object $entity
     */
    protected function updateInvoiceEntity($entity)
    {
        $this->get('app.billing_calculator')->totalWithoutTaxes($entity);
        $this->em->flush();
    }

}
