<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\EntityRemoveException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use App\Service\Export\Csv;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Gallery;
use App\Entity\ImageGallery;


abstract class AbstractController extends BaseAdminController 
{
    private $csv;

    public function __construct(Csv $csv)
    {
       $this->csv = $csv;
    }

    /**
     * @Route("/", name="easyadmin")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     *
     * @throws ForbiddenActionException
     */
    public function indexAction(Request $request)
    {
        $this->initialize($request);

        if (null === $request->query->get('entity')) {
            return $this->redirectToBackendHomepage();
        }

    
        $action = $request->query->get('action', 'list');
        if (!$this->isActionAllowed($action)) {
            throw new ForbiddenActionException(['action' => $action, 'entity_name' => $this->entity['name']]);
        }

        return $this->executeDynamicMethod($action.'<EntityName>Action');
    }

    /**
     * The method that is executed when the user performs a 'new' action on an entity.
     *
     * @return Response|RedirectResponse
     */
    protected function newAction()
    {

        $this->dispatch(EasyAdminEvents::PRE_NEW);

        $entity = $this->executeDynamicMethod('createNew<EntityName>Entity');

        $easyadmin = $this->request->attributes->get('easyadmin');
        $easyadmin['item'] = $entity;
        $this->request->attributes->set('easyadmin', $easyadmin);

        $fields = $this->entity['new']['fields'];

        $entity = $this->setAdministrable($entity);

        $newForm = $this->executeDynamicMethod('create<EntityName>NewForm', [$entity, $fields]);

        $newForm->handleRequest($this->request);
        if ($newForm->isSubmitted() && $newForm->isValid()) {

            $seoMetaData = $this->container->get('app.meta_data');
            // dump($seoMetaData);
            $entity = $seoMetaData->process($entity);

            $this->dispatch(EasyAdminEvents::PRE_PERSIST, ['entity' => $entity]);
            $this->executeDynamicMethod('persist<EntityName>Entity', [$entity, $newForm]);
            $this->dispatch(EasyAdminEvents::POST_PERSIST, ['entity' => $entity]);

            // TRANSLATION
            $values = $this->request->request->get(strtolower($easyadmin['entity']['name']));
            if(!empty($values)) {
                if(null != $entity->getId()) {
                    if (is_callable([$entity, 'getMetaTitle'])) {
                        $metaTitle = $entity->getMetaTitle();
                        $values['metaTitle'] = $metaTitle;
                    }
                    if (is_callable([$entity, 'getMetaDescription'])) {
                        $metaDescription = $entity->getMetaDescription();
                        $values['metaDescription'] = $metaDescription;
                    }
                    if (is_callable([$entity, 'getSlug'])) {
                        $slug = $entity->getSlug();
                        $values['slug'] = $slug;
                    }
                    if(isset($values['slug']) && null == $values['slug']) {
                        $values['slug'] = $entity->getSlug();
                    }

                    if(isset($easyadmin['entity']['translatable']) && $easyadmin['entity']['translatable']) {
                        $translator = $this->container->get('app.translator');
                        $translator->process(
                            $values
                            , $easyadmin['entity']
                            , $entity->getId()
                        );
                        $translator->write();
                    }
                }
            }

            return $this->redirectToReferrer();
        }

        $this->dispatch(EasyAdminEvents::POST_NEW, [
            'entity_fields' => $fields,
            'form' => $newForm,
            'entity' => $entity,
        ]);

        $parameters = [
            'form' => $newForm->createView(),
            'entity_fields' => $fields,
            'entity' => $entity,
        ];

        return $this->executeDynamicMethod('render<EntityName>Template', ['new', $this->entity['templates']['new'], $parameters]);
    }

    /**
     * The method that is executed when the user performs a 'edit' action on an entity.
     *
     * @return Response|RedirectResponse
     *
     * @throws \RuntimeException
     */
    protected function editAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_EDIT);

        $id = $this->request->query->get('id');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $entity = $easyadmin['item'];

        $entity = $this->setAdministrable($entity);

        if ($this->request->isXmlHttpRequest() && $property = $this->request->query->get('property')) {
            $newValue = 'true' === \mb_strtolower($this->request->query->get('newValue'));
            $fieldsMetadata = $this->entity['list']['fields'];

            if (!isset($fieldsMetadata[$property]) || 'toggle' !== $fieldsMetadata[$property]['dataType']) {
                throw new \RuntimeException(\sprintf('The type of the "%s" property is not "toggle".', $property));
            }

            $this->updateEntityProperty($entity, $property, $newValue);

            // cast to integer instead of string to avoid sending empty responses for 'false'
            return new Response((int) $newValue);
        }

        $fields = $this->entity['edit']['fields'];

        $editForm = $this->executeDynamicMethod('create<EntityName>EditForm', [$entity, $fields]);
        $deleteForm = $this->createDeleteForm($this->entity['name'], $id);

        // dump($this->request);die;
        $editForm->handleRequest($this->request);

        if (is_callable([$entity, 'setSlug'])) {
            $slug = $entity->setSlug(null);         
        }

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            
            $seoMetaData = $this->container->get('app.meta_data');
            // dump($seoMetaData);
            $entity = $seoMetaData->process($entity);

            $this->dispatch(EasyAdminEvents::PRE_UPDATE, ['entity' => $entity]);
            $this->executeDynamicMethod('update<EntityName>Entity', [$entity, $editForm]);
            $this->dispatch(EasyAdminEvents::POST_UPDATE, ['entity' => $entity]);
            
            // TRANSLATION
            $values = $this->request->request->get(strtolower($easyadmin['entity']['name']));
            if(!empty($values)) {
                if(null != $entity->getId()) {
                    if (is_callable([$entity, 'getMetaTitle'])) {
                        $metaTitle = $entity->getMetaTitle();
                        $values['metaTitle'] = $metaTitle;
                    }
                    if (is_callable([$entity, 'getMetaDescription'])) {
                        $metaDescription = $entity->getMetaDescription();
                        $values['metaDescription'] = $metaDescription;
                    }
                    if (is_callable([$entity, 'getSlug'])) {
                        $slug = $entity->getSlug();
                        $values['slug'] = $slug;
                    }
                    if(isset($values['slug']) && null == $values['slug']) {
                        $values['slug'] = $entity->getSlug();
                    }

                    if(isset($easyadmin['entity']['translatable']) && $easyadmin['entity']['translatable']) {
                        $translator = $this->container->get('app.translator');
                        
                        $translator->process(
                            $values
                            , $easyadmin['entity']
                            , $entity->getId()
                        );
                        $translator->write();
                    }
                }
            }

            if(isset($easyadmin['entity']['redirect']) && false === $easyadmin['entity']['redirect']) {
                
                return $this->redirectToRoute('easyadmin', array(
                    'action' => 'edit',
                    'id' => $easyadmin['item']->getId(),
                    'entity' => $easyadmin['entity']['name'],
                ));

            }
  
            return $this->redirectToReferrer();
        }

        $this->dispatch(EasyAdminEvents::POST_EDIT);

        $parameters = [
            'form' => $editForm->createView(),
            'entity_fields' => $fields,
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        ];

        return $this->executeDynamicMethod('render<EntityName>Template', ['edit', $this->entity['templates']['edit'], $parameters]);
    }

    protected function defineTheTranslationChain($values, $attributes, $id = null)
    {
        if(isset($attributes['translatable']) && $attributes['translatable']) {
            $translator = $this->container->get('app.translator');
            $translator->process(
                $values
                , $attributes
                , $id
            );
            $translator->write();
        }
    }

    /**
     * @param $entity
     */
    protected function setAdministrable($entity)
    {
        $params = $this->request->query->all();

        if (null == $entity->getId()) {
          if (is_callable([$entity, 'setUserCreated'])) {
              $entity->setUserCreated($this->getUser());
          }
        }

        if (is_callable([$entity, 'setUserLastModified'])) {
            $entity->setUserLastModified($this->getUser());
        }

        return $entity;
    }

    /**
     * The method that is executed when the user performs a 'list' action on an entity.
     *
     * @return Response
     */
    protected function listAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_LIST);

        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->entity['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'), $this->entity['list']['dql_filter']);

        $this->dispatch(EasyAdminEvents::POST_LIST, ['paginator' => $paginator]);

        $parameters = [
            'paginator' => $paginator,
            'fields' => $fields,
            'batch_form' => $this->createBatchForm($this->entity['name'])->createView(),
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
        ];

        return $this->executeDynamicMethod('render<EntityName>Template', ['list', $this->entity['templates']['list'], $parameters]);
    }

    public function exportAction()
    {
        $sortDirection = $this->request->query->get('sortDirection');
        if (empty($sortDirection) || !in_array(strtoupper($sortDirection), ['ASC', 'DESC'])) {
            $sortDirection = 'DESC';
        }

        // $this->entity['list']['dql_filter'] = 'entity.nature = 1';

        $queryBuilder = $this->createListQueryBuilder(
            $this->entity['class'],
            $sortDirection,
            $this->request->query->get('sortField'),
            $this->entity['list']['dql_filter']
        );

        $repository = $this->em->getRepository($this->entity['class']);
        // dump($repository);die;
        $columns = $repository->getColumnsForCsv($this->entity['class']);
        // dump($this->entity);
        // dump($columns);
        // dump($this->request->query->get('params'));
        // die;

        return $this->csv->getResponseFromQueryBuilder(
           $queryBuilder,
           $columns,
           $this->request->query->get('params'),
           'export_test2.csv'
        );
    }

    public function reorderAction()
    {
        if(isset($this->request->query->get('ext_filters')['entity.gallery'])) {
            $id = $this->request->query->get('ext_filters')['entity.gallery'];
            $repository = $this->em->getRepository(Gallery::class);
            $result = $repository->findOneById($id);

            $i = 0;
            foreach($result->getImageGalleries() as $media) {
                
                // dump($media->getImage()->getName());    
                // dump($media->getPosition());

                $i++;
                $media->setPosition($i);
                $this->em->persist($media);
            }
            $this->em->flush();
        }

        return $this->redirectToReferrer();
    }

    public function translateAction()
    {

        die('dieee');
        $translator = $this->container->get('app.translator');
        $i = 0;
        foreach($this->config['entities'] as $attributes) {
            if(isset($attributes['translatable']) && $attributes['translatable']) {
                $repository = $this->em->getRepository($attributes['class']);
                $results = $repository->createQueryBuilder('c')
                    ->getQuery()
                    ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
                foreach($results as $result) {
                    $translator->process(
                        $result
                        , $attributes
                        , $result['id']
                    );
                    if ($i > 0 && $i % 10 == 0) {
                        sleep(1);
                    }
                    $i++; 
                }
            }
        }
        $translator->write();

        return $this->redirectToReferrer();
    }

    /**
    * Allows applications to modify the entity associated with the item being
    * created while persisting it.
    *
    * @param object $entity
    */
   protected function persistEntity($entity)
   {
       $this->em->persist($entity);
       $this->em->flush();
   }

   /**
     * Allows applications to modify the entity associated with the item being
     * edited before updating it.
     *
     * @param object $entity
     */
    // protected function updateEntity($entity)
    // {
    //
    // }

    

}
