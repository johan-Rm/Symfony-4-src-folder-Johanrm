<?php

namespace App\Listeners;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use App\Entity\MediaObject;
use App\Services\MediaObjectTransformer;
use Vich\UploaderBundle\Event\Event;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Cocur\Slugify\Slugify;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Filesystem\Filesystem;


/**
 * MediaObjectListener
 */
class MediaObjectListener
{
    private $imagine;
    private $cacheManager;
    private $cdnHost;
    private $assetsMediaFolder;
    private $orm;
    private $container;

    public function __construct(ContainerInterface $container, CacheManager $cacheManager, FilterService $imagine, EntityManagerInterface $orm)
    {
        $this->cacheManager = $cacheManager;
        $this->imagine = $imagine;
        $this->orm = $orm;
        $this->container = $container;

        $this->assetsMediaFolder = $this->container->getParameter('assets.media.folder');
        $this->cdnHost = $this->container->getParameter('cdn.host');
    }

    public function onVichUploaderPreInject(Event $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof MediaObject) {
            return;
        }

        $filename = $entity->getFilename();

        $entity->setTmpFile($filename);
        $this->orm->flush();
    }

    public function onVichUploaderPostUpload(Event $event)
    {
        $entity = $event->getObject();
        $mapping = $event->getMapping();
        $slugify = new Slugify();
        $filesystem = new Filesystem();

        if($filesystem->exists($entity->getFile()->getRealPath())) {

            $filename = pathinfo($entity->getFilename(),  PATHINFO_FILENAME);
            $originalFilename = $entity->getFilename();
            $ext = pathinfo($entity->getFilename(), PATHINFO_EXTENSION);
            $filePath = $entity->getFile()->getPath();

            // $mimeType = $entity->getFile()->getMimeType();
            $dimensions = getimagesize($entity->getFile()->getPathname());
            $contentSize = $entity->getFile()->getSize();


           


            if (!$entity instanceof MediaObject) {
                $entity->setEncodingFormat($entity->getFile()->getExtension());
                $entity->setContentSize(null);
                $name = $this->setName($entity);
                $entity->setName($name);
                $entity->setFilename($filename);
                $entity->setOriginalFilename($filename);
            } else {

                // $sourceFilePath = $filePath . DIRECTORY_SEPARATOR . $filename . '.' . $ext;
                // $targetFilePath = $filePath . DIRECTORY_SEPARATOR . $filename . '.' . 'webp';

                // $cmd = [
                //     '/usr/bin/cwebp',
                //     '-q',
                //     '70',
                //     $sourceFilePath,
                //     '-o',
                //     $targetFilePath
                // ];
                
                // $process = new Process($cmd);
                // $process->setTimeout(900);
                // try {
                //     $process->mustRun();
                //     // $filesystem->remove($sourceFilePath);
                // } catch (ProcessFailedException $exception) {

                //     throw new \RuntimeException($exception->getMessage());
                // }
                
                // $sourceFilePath = $filePath . DIRECTORY_SEPARATOR . $filename . '.' . 'webp';
                // $targetFilePath = $filePath . DIRECTORY_SEPARATOR . $filename . '.' . 'jpg';

                // $cmd = [
                //     '/usr/bin/convert',
                //     $sourceFilePath,
                //     $targetFilePath
                // ];

                // $targetFilename = pathinfo($targetFilePath,  PATHINFO_BASENAME);
                
                // $process = new Process($cmd);
                // $process->setTimeout(900);
                // try {

                //     $process->mustRun();
                    
                //     // dump($process->getOutput());
                //     // $mimeType = $entity->getFile()->getMimeType();
                //     // pour le moment tout est converti en jpg
                //     $entity->setEncodingFormat('image/jpg');
                //     $entity->setContentSize($contentSize);
                //     $entity->setDimensions($dimensions);
                //     $name = $this->setName($entity);
                //     $entity->setName($filename);
                //     $entity->setFilename($targetFilename);
                //     $entity->setOriginalFilename($originalFilename);

                //     // $filesystem->remove($sourceFilePath);
                    
                    
                // } catch (ProcessFailedException $exception) {
                //     throw new \RuntimeException($exception->getMessage());
                // }
   
                $entity->setEncodingFormat('image/jpg');
                $entity->setContentSize($contentSize);
                $entity->setDimensions($dimensions);
                $name = $this->setName($entity);
                $entity->setName($filename);
                $entity->setFilename($originalFilename);
                $entity->setOriginalFilename($originalFilename);

            }
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof MediaObject) {
            return;
        }
        $changeSet = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($entity);

        // dump($changeSet);die;
        if(!array_key_exists("filename", $changeSet)){
            return;
        }

        try {
            // $this->cacheManager->remove($this->assetsMediaFolder.'/'.$entity->getTmpFile());
            $this->cacheManager->resolve($this->assetsMediaFolder.'/'.$entity->getFilename(), null);

        } catch (\Exception $e) {

        }

        $this->setMultiFormat($entity);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof MediaObject) {
            return;
        }

        $target = $this->assetsMediaFolder.'/'.$entity->getFilename();
        
        try {
            $this->cacheManager->remove($target);
        } catch (\Exception $e) {

        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $filesystem = new Filesystem();
        $entity = $args->getObject();
        if (!$entity instanceof MediaObject) {
            return;
        }

        $filePath = $this->assetsMediaFolder . DIRECTORY_SEPARATOR . $entity->getFilename();
        $this->imagine->getUrlOfFilteredImage($filePath, 'original');  
        $realFilePath = $entity->getFile()->getRealPath();
        $filePathCache = $this->container
            ->getParameter('assets.path') . DIRECTORY_SEPARATOR;
        $filePathCache.= 'media/cache' . DIRECTORY_SEPARATOR . 'original';
        $filePathCache.= DIRECTORY_SEPARATOR .  $filePath;
        $filesystem->copy($filePathCache, $realFilePath);
        $filesystem->remove($filePathCache);

        $this->setMultiFormat($entity);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof MediaObject) {
            return;
        }

        $url = $this->setMediaUrl($entity);
        $entity->setUrl($url);

        $name = $this->setName($entity);
        $entity->setName($name); 
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof MediaObject) {
            return;
        }

        // $slugify = new Slugify();
        // $filename = $slugify->slugify($entity->getFilename());
        // $entity->setFilename($filename);

        $url = $this->setMediaUrl($entity);
        $entity->setUrl($url);
        $name = $this->setName($entity);
        $entity->setName($name);
        // dump($entity);die;
    }

    private function setMediaUrl($entity)
    {
        $url = $entity->getUrl();
        if($entity->getEncodingFormat() !== 'video/youtube') {

            return $this->cdnHost . $this->assetsMediaFolder . '/' . $entity->getFilename();
        }

        return $url;
    }

    private function setName($entity) 
    {
        if(empty($entity->getName())) {
            $slugify = new Slugify();
            $name = pathinfo($entity->getFilename(),  PATHINFO_FILENAME);
            $name = $slugify->slugify($name);

            return $name;
        }

        return $entity->getName();
    }

    private function setFilename($entity) 
    {
 		if(!empty($entity->getName())) {
			$ext = pathinfo($entity->getFilename(), PATHINFO_EXTENSION);
			$entity->setFilename($entity->getName() . '.' . $ext);
        }
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
