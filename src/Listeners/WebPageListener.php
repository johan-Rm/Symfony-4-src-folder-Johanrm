<?php

namespace App\Listeners;

use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Entity\WebPage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\Filesystem\Filesystem;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;


/**
 * WebPageListener
 */
class WebPageListener
{
    private $orm;
    private $viewHost;
    private $container;
    private $imagine;
    private $cacheManager;
    private $assetsMediaFolder;

    public function __construct(EntityManagerInterface $orm, ContainerInterface $container, FilterService $imagine, CacheManager $cacheManager)
    {
        $this->orm = $orm;
        $this->container = $container;
        $this->imagine = $imagine;
        $this->cacheManager = $cacheManager;

        $this->assetsMediaFolder = $this->container->getParameter('assets.media.folder');
        $this->viewHost = $this->container->getParameter('view.host');
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof WebPage) {
            return;
        }

        if(empty($entity->getAlternativeHeadline())) {
            $entity->setAlternativeHeadline($entity->getHeadline());
        }

        $url = $this->setWebPageUrl($entity->getSlug());
        $entity->setUrl($url);
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof WebPage) {
            return;
        }

        // if(empty($entity->getUserCreated())) {
        //     $user = $this->token->getToken()->getUser();
        //     $entity->setUserCreated($user);
        // }

        if(empty($entity->getAlternativeHeadline())) {
            $entity->setAlternativeHeadline($entity->getHeadline());
        }
        
        $url = $this->setWebPageUrl($entity->getSlug());
        $entity->setUrl($url);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof WebPage) {
            return;
        }

        if(true === $entity->getRegenerateFormat()) {
            if(null !== $entity->getGallery()) {
                foreach($entity->getGallery()->getImageGalleries() as $media) {
                    ini_set('max_execution_time', 3600);
                    $this->setMultiFormat($media->getImage());    
                }    
            }
            
            if(null !== $entity->getPrimaryImage()) {
                $this->setMultiFormat($entity->getPrimaryImage());
            }
            if(null !== $entity->getSecondaryImage()) {
                $this->setMultiFormat($entity->getSecondaryImage());    
            }
        }

        if (php_sapi_name() !== "cli") {

            $nuxtJsRouter = $this->container->get('app.nuxtjs.router');
            $nuxtJsRouter->generate($entity, ['route' => 'web_pages']);
            // $exportApiToJson = $this->container->get('app.export.api_to_json');
            // $entityName = 'web_pages';
            // $slug = $entity->getSlug();                
            // $options = [
            //     'query' => [
            //         'slug' => $slug
            //     ]
            // ];
            // $exportApiToJson->generateOne($entityName, $options);
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof WebPage) {
            return;
        }

        if (php_sapi_name() !== "cli") {
            $nuxtJsRouter = $this->container->get('app.nuxtjs.router');
            $nuxtJsRouter->generate($entity, ['route' => 'web_pages']);
            // $exportApiToJson = $this->container->get('app.export.api_to_json');
            // $entity = 'web_pages';
            // $slug = $entity->getSlug();                
            // $options = [
            //     'query' => [
            //         'slug' => $slug
            //     ]
            // ];
            // $exportApiToJson->generateOne($entity, $options); 
        }
       
    }

    private function setWebPageUrl($slug)
    {
        return $this->viewHost . '/' . $slug;
    }


    private function setMultiFormat($entity)
    {
        if(null !== $entity) {
            $filesystem = new Filesystem();
            $formats = array('image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/jp2', 'image/jxr');
            
            $filePath = $this->assetsMediaFolder . DIRECTORY_SEPARATOR . $entity->getFilename();
            if($filesystem->exists($this->container->getParameter('assets.path') . DIRECTORY_SEPARATOR . $filePath)) {
                if(in_array($entity->getEncodingFormat(), $formats)) {
                    $filter_sets = $this->container->getParameter('liip_imagine.filter_sets');
                    foreach($filter_sets as $key => $filter) {

                        if('original' !== $key) {
                            $filePathCache = $this->container->getParameter('assets.path') . DIRECTORY_SEPARATOR;
                            $filePathCache.= 'media/cache' . DIRECTORY_SEPARATOR . $key;
                            $filePathCache.= DIRECTORY_SEPARATOR .  $filePath;

                            if($filesystem->exists($filePathCache)) {
                                $this->cacheManager->remove($filePath, $key);
                            }

                            $this->imagine->getUrlOfFilteredImage($filePath, $key);  
                        }
                        
                    }
                }
            }
        }
    }
}
