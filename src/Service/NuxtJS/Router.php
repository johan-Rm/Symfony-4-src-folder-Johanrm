<?php

namespace App\Service\NuxtJS;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Finder\Finder;
use Doctrine\Common\Util\Inflector;
use App\Entity\Article;
use App\Entity\WebPage;
use App\Entity\Accommodation;
use App\Entity\AccommodationType;



class Router
{
    private $container;
    private $em;

    public function __construct(ContainerInterface $container, EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    /**
    * Le router symfony doit se baser sur la configuration nuxtjs ?
    * ou du moins il faudrait stocker les regles des routes quelques part
    **/
    public function generate($entity, $options = []) 
    {
        /*
            "pathFile": "/home/www/graines-digitales/immobiliere-essaouira/cms/../view",
    "slug": "villa-golf",
    "entity": "accommodationType",
    "route": "accommodation_types",
    "path": "/accommodation_types?slug=villa-golf",
    "filename": "accommodationType-villa-golf",
    "baseUrl": "/vente/"
    */
        
        $slug   = $entity->getSlug();
        
        $entityName = Inflector::camelize((new \ReflectionClass($entity))->getShortName());
        
        $route  = Inflector::tableize(Inflector::pluralize($entityName));
        if(isset($options['route'])) {
            $route = $options['route'];
        }
        $path   = '/' . $route . '?slug=' . $slug;
        $filename = $entityName . '-' . $slug;
        
        $pathFile = $this->container->getParameter('view.project_dir');

        $filesystem = new Filesystem();
        if(!$filesystem->exists($this->container->getParameter('view.build_routes.path'))) {
            $filesystem->mkdir($this->container->getParameter('view.build_routes.path'));
            $filesystem->mkdir($this->container->getParameter('view.build_routes.path') . '/process/');
            $filesystem->mkdir($this->container->getParameter('view.build_routes.path') . '/waiting/');
        }


        $content = array(
            'pathFile' => $pathFile,
            'slug' => $slug,
            'entity' => $entityName,
            'route' => $route,
            'path' => $path,
            'filename' => $filename,
            'baseUrl' => $this->getBaseUrl($entity, $options)
        );

        
        $waitingFolder = $this->container->getParameter('view.build_routes.path') . '/waiting/';
        $json = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);

        
        try {
            $filesystem->dumpFile(
                $waitingFolder . $filename . '.json'
                , $json
            );

        } catch (IOExceptionInterface $exception) {
            dump("Generate Routes : An error occurred while generating your routes at ".$exception->getPath());
            die;
        }

    }

    private function getBaseUrl($entity, $options) 
    {
        // dump($options);die;
        // if article => /actualite/slug-article
        if ($entity instanceof Article) {

            return '/actualite/' . $entity->getCategory()->getSlug() . '/';
        }
        // if webPage => /slug-webPage
        if ($entity instanceof WebPage) {

            return '/';
        }
        // id accommodationType => /nature/slug-accommodation-type
        if ($entity instanceof AccommodationType) {

            return '/' . $options['nature']->getSlug() . '/';
        }
        // if accommodation => /nature/type/slug-accommodation
        if ($entity instanceof Accommodation) {

            return '/' . $options['nature']->getSlug() 
                        . '/' . $entity->getType()->getSlug() . '/';
        } 
    }

}
