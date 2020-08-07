<?php

namespace App\Listeners;

use Twig\Environment;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use App\Entity\Accommodation;
use App\Entity\AccommodationDetail;
use App\Entity\AccommodationType;
use App\Entity\WebPage;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;
use Doctrine\Common\Util\Inflector;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Cocur\Slugify\Slugify;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Process\Process;
use Liip\ImagineBundle\Service\FilterService;


/**
 * AccommodationListener
 */
class AccommodationListener 
{
    private $knpSnappy;
    private $orm;
    private $assetsPdfPath;
    private $templating;
    private $cdnHost;
    private $container;
    private $translator;
    private $imagine;
    private $assetsMediaFolder;
    private $cacheManager;


    public function __construct(Pdf $knpSnappy, EntityManagerInterface $orm, ContainerInterface $container, Environment $templating, TranslatorInterface $translator, FilterService $imagine, CacheManager $cacheManager)
    {
        $this->knpSnappy = $knpSnappy;
        $this->orm = $orm;
        $this->templating = $templating;
        $this->container = $container;
        $this->translator = $translator;
        $this->imagine = $imagine;
        $this->cacheManager = $cacheManager;


        $this->assetsPdfPath = $this->container->getParameter('assets.pdf.path');
        $this->assetsMediaFolder = $this->container->getParameter('assets.media.folder');
        $this->cdnHost = $this->container->getParameter('cdn.host');
    }


    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Accommodation) {
            return;
        }

        if (php_sapi_name() !== "cli") {
            $nuxtJsRouter = $this->container->get('app.nuxtjs.router');
            $nuxtJsRouter->generate(
                $entity
                , [
                    'route' => 'full_accommodations'
                    , 'nature' => $entity->getNature()
                ]
            );
            $nuxtJsRouter->generate(
                $entity->getType()
                , [
                    'nature' => $entity->getNature()
                ]
            );
            
            if(null == $entity->getReference()
                || true === $entity->getRegenerateReference()
            ) {
                $reference = $this->createReference($entity);
                $entity->setReference($reference);
            }
            
            if(true === $entity->getRegenerateFormat()) {
                if(null !== $entity->getGallery()) {
                    ini_set('max_execution_time', 3600);
                    foreach($entity->getGallery()->getImageGalleries() as $media) {
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
            
        }
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Accommodation) {
            return;
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Accommodation) {
            return;
        }


        if (php_sapi_name() !== "cli") {

            $this->createPdf($entity);

            $nuxtJsRouter = $this->container->get('app.nuxtjs.router');
            $nuxtJsRouter->generate(
                $entity
                , [ 
                        'route' => 'full_accommodations'
                        , 'nature' => $entity->getNature()
                    ]
                );
            $nuxtJsRouter->generate(
                $entity->getType()
                , [
                    'nature' => $entity->getNature()
                ]
            );

            // $exportApiToJson = $this->container->get('app.export.api_to_json');
            // $entityName = 'accommodations';
            // $slug = $entity->getSlug(); 
            // $category = $entity->getType()->getSlug();
            // $options = [
            //     'query' => [
            //         'slug' => $slug,
            //         'category' => $category
            //     ]
            // ];
            // $exportApiToJson->generateOne($entityName, $options);

            if(null == $entity->getReference()) {
                $reference = $this->createReference($entity);
                $entity->setReference($reference);
            }
        }
       
    }

    private function createPdf($entity)
    {
        $filesystem = new Filesystem();
        if($filesystem->exists($this->assetsPdfPath . '/' . $entity->getSlug() . '.pdf')) {
            $filesystem->remove($this->assetsPdfPath . '/' . $entity->getSlug() . '.pdf');
        }
 
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', 300);
        $assetsMediaPath = $this->container->getParameter('assets.media.folder');
        $assetsImagesPdfPath = $this->container->getParameter('assets.pdf.path') . DIRECTORY_SEPARATOR . 'images';
        $logo = $assetsImagesPdfPath . DIRECTORY_SEPARATOR . 'logo_bg_primary_2.png';
        if(null !== $entity->getGallery()) {
            foreach($entity->getGallery()->getImageGalleries() as $media) {
                $fileNameWithExtension = $media->getImage()->getFilename();
                $ext = pathinfo($fileNameWithExtension, PATHINFO_EXTENSION);
                $file = basename($fileNameWithExtension);
                $filename = basename($fileNameWithExtension, ".".$ext);
                
                $sourceFilePath = $this->container->getParameter('assets.path') . DIRECTORY_SEPARATOR . $assetsMediaPath . DIRECTORY_SEPARATOR . $filename . '.' . $ext;
                $targetFilePath = $assetsImagesPdfPath 
                    . DIRECTORY_SEPARATOR . $filename . '.' . $ext . '.' . 'png';

                if($filesystem->exists($sourceFilePath) && !$filesystem->exists($targetFilePath)) {
                    // $this->convertWebpToPng($sourceFilePath, $targetFilePath);
                }                       
            }
        }

        $host = $assetsImagesPdfPath . DIRECTORY_SEPARATOR;

        $plan = false;
        foreach($entity->getPdfs() as $pdf) {
            if('plan' === $pdf->getType()->getSlug()) {
                $plan = $host . 'uploads/document/files/' . $pdf->getFilename();
            }
        }

        $html = $this->templating->render(
            'components/technical-card.html.twig',
            array(
                'accommodation' => $entity,
                'logo' => $logo,
                'host' => $host,
                'plan' => $plan,
                'locale' => 'fr'
            )
        );


        $filename = $this->assetsPdfPath . '/' . $entity->getSlug() . '.pdf';
 
        $this->knpSnappy->generateFromHtml(
            $html,
            $filename
        );
    }

    private function createPdfEnglishVersionTemp($entity)
    {   
        $name = $this->translator->trans('accommodation.' . $entity->getSlug(), [], 'accommodations-slug', 'en');

        $filesystem = new Filesystem();
        if($filesystem->exists($this->assetsPdfPath . '/' . $name . '.pdf')) {
            $filesystem->remove($this->assetsPdfPath . '/' . $name . '.pdf');
        }
 
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', 300);

        $assetsMediaPath = $this->container->getParameter('assets.media.folder');
        $assetsImagesPdfPath = $this->container->getParameter('assets.pdf.path') . DIRECTORY_SEPARATOR . 'images';
        $logo = $assetsImagesPdfPath . DIRECTORY_SEPARATOR . 'logo_bg_primary_2.png';
        foreach($entity->getGallery()->getImageGalleries() as $media) {
            $fileNameWithExtension = $media->getImage()->getFilename();
            $ext = pathinfo($fileNameWithExtension, PATHINFO_EXTENSION);
            $file = basename($fileNameWithExtension);
            $filename = basename($fileNameWithExtension, ".".$ext);
            
            $sourceFilePath = $assetsMediaPath . DIRECTORY_SEPARATOR . $filename . '.' . $ext;
            $targetFilePath = $assetsImagesPdfPath 
                . DIRECTORY_SEPARATOR . $filename . '.' . $ext . '.' . 'png';

            if($filesystem->exists($sourceFilePath) && !$filesystem->exists($targetFilePath)) {
                // $this->convertWebpToPng($sourceFilePath, $targetFilePath);
            }                     
        }

        $host = $assetsImagesPdfPath . DIRECTORY_SEPARATOR;

        $plan = false;
        foreach($entity->getPdfs() as $pdf) {
            if('plan' === $pdf->getType()->getSlug()) {
                $plan = $host . 'uploads/document/files/' . $pdf->getFilename();
            }
        }

        $html = $this->templating->render(
            'components/technical-card.html.twig',
            array(
                'accommodation' => $entity,
                'logo' => $logo,
                'host' => $host,
                'plan' => $plan,
                'locale' => 'en'
            )
        );

        
        $filename = $this->assetsPdfPath . '/' . $name . '.pdf';
 
        $this->knpSnappy->generateFromHtml(
            $html,
            $filename
        );
    }

    public function createReference($entity) 
    {
        $reference = null;
        $nature = $entity->getNature();
        $type = $entity->getType();
        if('vente' == $nature->getSlug()) {
            
            $results = $this->orm->getRepository(Accommodation::class)
            ->findBySellingCriteria(['nature' => $nature, 'type' => $type]);
            $count = count($results) + 1;
            $initials = null;
            $initials.=  strtoupper(substr($nature->getSlug(), 0, 1));
            $words = explode("-", $type->getSlug());
            foreach($words as $word) {
                $initials.= strtoupper(substr($word, 0, 1));
            }
            $reference =  $initials . $count;

        } else if('location' == $nature->getSlug()) {

            $duration = $entity->getDuration();
            $results = $this->orm->getRepository(Accommodation::class)
            ->findByRentingCriteria(['nature' => $nature, 'duration' => $duration]);
            $count = count($results) + 1;
            $initials = null;
            $initials.=  strtoupper(substr($nature->getSlug(), 0, 1));
            $words = explode("-", $duration->getSlug());
            $initial = null;
            foreach($words as $word) {
                $initial.= strtoupper(substr($word, 0, 1));
            }
            $initials.=  $initial;
            $words = explode("-", $type->getSlug());
            $initial = null;
            foreach($words as $word) {
                $initial.= strtoupper(substr($word, 0, 1));
            }
            $initials.=  $initial;
            $reference =  $initials . $count;
        }

        return $reference;
    }

    private function convertWebpToPng($sourceFilePath, $targetFilePath)
    {
         $cmd = [
            '/usr/bin/dwebp',
            $sourceFilePath,
            '-o',
            $targetFilePath
        ];

        $process = new Process($cmd);
        $process->setTimeout(900);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw new \RuntimeException($exception->getMessage());
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
