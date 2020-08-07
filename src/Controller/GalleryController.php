<?php

namespace App\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use App\Entity\Gallery;
use App\Entity\ImageGallery;
use Symfony\Component\HttpFoundation\JsonResponse;


class GalleryController extends AbstractController
{
    /**
     * The method that is executed when the user performs a 'list' action on an entity.
     *
     * @return Response
     */
    protected function listGalleryAction()
    {
        // $this->entity['list']['dql_filter'] = "entity.component =  1";
        // $this->dispatch(EasyAdminEvents::PRE_LIST);

        // $fields = $this->entity['list']['fields'];
        // $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->entity['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'), $this->entity['list']['dql_filter']);

        // $this->dispatch(EasyAdminEvents::POST_LIST, ['paginator' => $paginator]);

        // $parameters = [
        //     'paginator' => $paginator,
        //     'fields' => $fields,
        //     'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
        // ];

        // return $this->executeDynamicMethod('render<EntityName>Template', ['list', $this->entity['templates']['list'], $parameters]);


        //         $this->dispatch(EasyAdminEvents::PRE_LIST);

        // $fields = $this->entity['list']['fields'];
        // $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->entity['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'), $this->entity['list']['dql_filter']);

        // $this->dispatch(EasyAdminEvents::POST_LIST, ['paginator' => $paginator]);

        // $parameters = [
        //     'paginator' => $paginator,
        //     'fields' => $fields,
        //     'batch_form' => $this->createBatchForm($this->entity['name'])->createView(),
        //     'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
        // ];

        // return $this->executeDynamicMethod('render<EntityName>Template', ['list', $this->entity['templates']['list'], $parameters]);

        // dump($this->entity);die;

        return parent::listAction();

    }
 
    /**
     * The method that is executed when the user performs a 'new' action on an entity.
     *
     * @return Response|RedirectResponse
     *
     * @throws \RuntimeException
     */
    protected function newGalleryAction()
    {
        // dump('yo controller');
        // dump($this->request);
        // die();

        return parent::newAction();
    }

    /**
     * The method that is executed when the user performs a 'edit' action on an entity.
     *
     * @return Response|RedirectResponse
     *
     * @throws \RuntimeException
     */
    protected function editGalleryAction()
    {   
        // $editForm->handleRequest($this->request);
        // if ($editForm->isSubmitted() && $editForm->isValid()) {

        //     // dump('yo controller');
        //     dump($this->request);
        //     // die();
        //     $this->syncGallery($this->request);
        // }

        return parent::editAction();
    }
    
    /**
     * * @Route("/sort/{id}/{position}", name="easyadmin_sort")
     *
     * @param Request $request
     *
     * @return RedirectJsonResponse|JsonResponse
     *
     * @throws ForbiddenActionException
     */
    public function sortAction(Request $request, $id, $position)
    {

        // This is optional.
        // Only include it if the function is reserved for ajax calls only.
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Error'),
            JsonResponse::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $imageGallery = $em->getRepository(ImageGallery::class)->find($id);
        
        $imageGallery->setPosition($position);
        
        $em->persist($imageGallery);
        $em->flush();


        return new JsonResponse(array(
            'status' => 'Ajaxxxx',
            'message' => 'Ajaxxxx'),
        JsonResponse::HTTP_OK);
    }

    private function syncGallery($request)
    {   
 
        $galleryId = $this->request->query->get('id');
        $gallery = $this->em->getRepository(Gallery::class)->find($galleryId);
        
        $imageGallery = $this->em->getRepository(ImageGallery::class)->findOneBy(['gallery' => $galleryId]);
        
        if(null === $imageGallery) {
            $imageGallery = new ImageGallery();
        }

        // dump($imageGallery);die;
        $i = 0;
        foreach($gallery->getImages() as $image) {
            $i++;


            $imageGallery->setImage($image);
            $imageGallery->setGallery($gallery);
            $imageGallery->setPosition($i);
            $this->em->persist($imageGallery);

            
        }
        $this->em->flush();
        dump($imageGallery);
        dump($gallery);

        die;
    }
    

}
