<?php // src/Controller/DefaultController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Doctrine\Common\Util\Inflector;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\JsonResponse;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations as Rest;
// use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\View;

/**
 * @Route("/form")
 */
class SchemaController extends FOSRestController
{


     /**
     *
     * @Get("/{entity}", name="schema_form")
     * @Rest\View()
     * @Method({"GET","OPTIONS"})
     *
     **/
     public function getSchemaFormAction(Request $request)
     {
         /**
         * init Tools config
         */
        $entityName =  ucfirst(Inflector::camelize($request->get('entity')));
        $entityNameFormType = 'App\Form\\' . $entityName . 'Type';
        $entityId = $request->get('id');
        $manager = $this->getDoctrine()->getManager();
        $repository = $manager->getRepository('App:' . $entityName);
        $entity = $repository->findOneById($entityId);
        $form = $this->createForm($entityNameFormType, $entity);
        $metadata = $manager->getClassMetadata('App:' . $entityName);
        /**
        * init JSON config files
        */
        $formConfig = array();
        $indexName = $form->getConfig()->getName();
        $formConfig[$indexName] = [];
        $formConfig[$indexName]['formName'] = $form->getConfig()->getOption('form_name');
        $formConfig[$indexName]['entityName'] = $entityName;
        /**
         * get params VALUES
         */
        $values = $this->getFormConfigValues($entity);
        $formConfig[$indexName]['values'] = $values;
        /**
        * get params SCHEMA
        */
        $schema = $this->getFormConfigSchema($form, $metadata, $repository);
        $formConfig[$indexName]['schema'] = $schema;
        /**
        * get params OPTIONS
        */
        $formConfig[$indexName]['options'] = array(
         'validateAfterLoad' => true, // au submit
         'validateAfterChanged' => true, // après modif pour un champ donné
        );

        /**
        * Create JSON View
        */
        $view = View::create($formConfig[$indexName]);
        $view->setFormat('json');

        return $view;
    }

    private function getFormConfigValues($entity)
    {
        /**
        * get params VALUES
        */
        // if(!is_object($entity)) {
        //  $entity = array();
        //  foreach($schema as $key=>$value) {
        //      $entity[$key] = null;
        //  }
        // } else {
        //  $serializer = $this->container->get('jms_serializer');
        //  $entity = $serializer->toArray($entity);
        //  if(isset($entity['password'])) {
        //      $entity['password'] = '';
        //  }
        // }

         $serializer = $this->container->get('jms_serializer');
         $entity = $serializer->toArray($entity);
         if(isset($entity['password'])) {
             $entity['password'] = '';
         }

        return $entity;
    }

    private function getFormConfigSchema($form, $metadata, $repository)
    {
        $annotationReader = new AnnotationReader();
        $annotations = array();
        foreach($metadata->fieldMappings as $field) {
          $reflectionProperty = new \ReflectionProperty($repository->getClassName(), $field['fieldName']);
          $propertyAnnotations = $annotationReader->getPropertyAnnotations($reflectionProperty);
          $indexName = Inflector::tableize($reflectionProperty->getName());
            $properties = array();
          foreach($propertyAnnotations as $property) {
              $reflectionClass = new \ReflectionClass($property);
              $properties[strtolower($reflectionClass->getShortName())] = $property;
          }
          $annotations[$indexName] = $properties;
        }

        $schema = array();
        $schema['fields'] = array();
        $schema['orders'] = array();
        $schema['options'] = $form->getConfig()->getOptions();
        foreach($form->getIterator() as $key => $item) {

            $properties = array();
            $options = $item->getConfig()->getOptions();
            $type = $item->getConfig()->getType()->getInnerType();
            $reflectionClass = new \ReflectionClass($type);
            /**
            * SPECIF PROCESS BETWEEN WITH
            * MAPPED FIELD AND NON MAPPED FIELD
            */
            if(isset($annotations[$key]['column'])) {
                /**
                * MAPPED FIELD
                */
                $annotations[$key]['column']->value = (isset($entity[$key])? $entity[$key]: null);
                $annotations[$key]['column']->type = $reflectionClass->getShortName();

                $properties = $annotations[$key];
            } else {
                /**
                * NON MAPPED FIELD
                */
                $property = array();
                $property[$key]['column'] = new \stdClass();
                $property[$key]['column']->type = $reflectionClass->getShortName();
                if(!isset($property[$key]['column']->name)) {
                 $property[$key]['column']->name = $key;
                }
                $properties = $property[$key];
            }
            $schema['fields'][$key]['properties'] = $properties;
            $schema['fields'][$key]['options'] = $options;
        }


        return $schema;
    }

    public function jsonRender($content, $status= true, $encode = true, $type = 'array')
    {
        $jsonContent = $content;
        if ($encode) {
            if ($type == 'array') {
                $jsonContent = (array) $jsonContent;
            } elseif ($type == 'entities') {

                $jsonContent = $this->serializeEntities($jsonContent, true);
            }
        } else {
        	$jsonContent = json_decode($jsonContent);
        }

        $response = new JsonResponse();
        $response->setContent(json_encode($content));
        // $response->headers->replace($this->headers);

        return  $response;

    }

    protected function serializeEntities($entities, $array = false)
     {
     	$jsonContent = $this->serializer->serialize($entities, 'json');
     	if($array) {
     		$jsonContent = json_decode($jsonContent);
     	}

     	return $jsonContent;
     }
}
