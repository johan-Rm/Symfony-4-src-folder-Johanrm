<?php

namespace App\Listeners;

use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\Filesystem\Filesystem;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;


/**
 * WebPageListener
 */
class ArticleListener
{
    private $orm;
    private $token;
    private $viewHost;
    private $container;
    private $imagine;
    private $cacheManager;
    private $assetsMediaFolder;

    public function __construct(EntityManagerInterface $orm, TokenStorageInterface $token, ContainerInterface $container, FilterService $imagine, CacheManager $cacheManager)
    {
        $this->orm = $orm;
        $this->token = $token;
        $this->container = $container;
        $this->imagine = $imagine;
        $this->cacheManager = $cacheManager;

        $this->assetsMediaFolder = $this->container->getParameter('assets.media.folder');
        $this->viewHost = $this->container->getParameter('view.host');
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Article) {
            return;
        }

        if(empty($entity->getAlternativeHeadline())) {
            $entity->setAlternativeHeadline($entity->getHeadline());
        }

        if(empty($entity->getArticleResume())) {
            $resume = strip_tags($entity->getArticleBody());
            $resume = substr($resume, 0, 350);
            $resume = html_entity_decode($resume, ENT_QUOTES);
            $entity->setArticleResume(trim($resume));
        }
        
        $url = $this->setArticleUrl($entity->getSlug());
        $entity->setUrl($url);
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Article) {
            return;
        }
        
        if(empty($entity->getAlternativeHeadline())) {
            $entity->setAlternativeHeadline($entity->getHeadline());
        }

        if(empty($entity->getArticleResume())) {
            $resume = strip_tags($entity->getArticleBody());
            $resume = substr($resume, 0, 350);
            $resume = html_entity_decode($resume, ENT_QUOTES);
            $entity->setArticleResume(trim($resume));
        }

        $url = $this->setArticleUrl($entity->getSlug());
        $entity->setUrl($url);
        // $this->orm->flush();
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Article) {
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
            $nuxtJsRouter->generate($entity, ['route' => 'full_articles']);  
            $nuxtJsRouter->generate($entity->getCategory(), ['route' => 'tags']);
            // $exportApiToJson = $this->container->get('app.export.api_to_json');
            // $entityName = 'articles';
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
        if (!$entity instanceof Article) {
            return;
        }

        if (php_sapi_name() !== "cli") {
            $nuxtJsRouter = $this->container->get('app.nuxtjs.router');
            $nuxtJsRouter->generate($entity, ['route' => 'full_articles']);
            // $nuxtJsRouter->generate('actualite');
            // $exportApiToJson = $this->container->get('app.export.api_to_json');
            // $entityName = 'articles';
            // $slug = $entity->getSlug();                
            // $options = [
            //     'query' => [
            //         'slug' => $slug
            //     ]
            // ];
            // $exportApiToJson->generateOne($entityName, $options);
        }
    }

    private function setArticleUrl($slug)
    {
        return $this->viewHost . '/actualite/' . $slug;
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
