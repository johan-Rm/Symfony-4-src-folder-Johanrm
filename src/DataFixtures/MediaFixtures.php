<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\MediaObject;
use App\Entity\DocumentObject;
use App\Entity\DocumentObjectType;
use App\Entity\Gallery;
use App\Service\Export\CsvToArray;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Cocur\Slugify\Slugify;


class MediaFixtures extends AbstractFixtures
{
    private $csvToArray;

    public function __construct(CsvToArray $csvToArray)
    {
        $this->csvToArray = $csvToArray;
    }

    public function load(ObjectManager $manager)
    {
        if(!$this->empty) {
            $this->loadLogos($manager);
            $this->loadVideos($manager);
            $this->loadRealEstateAgents($manager);
            $this->loadDocuments($manager);
            $this->loadAll($manager);
            $this->loadAccommodationsImages($manager);
        }
    }

    public function loadAll($manager) 
    {
        $galleryHorizontal = new Gallery();
        $galleryHorizontal->setName('Demo galerie horizontal');
        $manager->persist($galleryHorizontal);
        $manager->flush();
        $galleryVertical = new Gallery();
        $galleryVertical->setName('Demo galerie vertical');
        $manager->persist($galleryVertical);
        

        $media = new MediaObject();
        $media->setName('28ES_comp.jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/28ES_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('28ES_comp.jpg');
        $media->setFilename('28ES_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        
        $galleryHorizontal->addImage($media);
        
        $media = new MediaObject();
        $media->setName('29ES_comp.jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/29ES_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('29ES_comp.jpg');
        $media->setFilename('29ES_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        
        $galleryHorizontal->addImage($media);

        $media = new MediaObject();
        $media->setName('30ES_comp.jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/30ES_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('30ES_comp.jpg');
        $media->setFilename('30ES_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        
        $galleryHorizontal->addImage($media);       

        $media = new MediaObject();
        $media->setName('31ES_comp.jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/31ES_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('31ES_comp.jpg');
        $media->setFilename('31ES_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        
        $galleryHorizontal->addImage($media);

        $media = new MediaObject();
        $media->setName('13ES_comp_360x560.jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/13ES_comp_360x560.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('13ES_comp_360x560.jpg');
        $media->setFilename('13ES_comp_360x560.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        
        $galleryVertical->addImage($media);

        $media = new MediaObject();
        $media->setName('14ES_comp_360x560.jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/14ES_comp_360x560.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('14ES_comp_360x560.jpg');
        $media->setFilename('14ES_comp_360x560.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        
        $galleryVertical->addImage($media);

        $media = new MediaObject();
        $media->setName('16ES_comp_360x560.jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/16ES_comp_360x560.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('16ES_comp_360x560.jpg');
        $media->setFilename('16ES_comp_360x560.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        
        $galleryVertical->addImage($media);

        $media = new MediaObject();
        $media->setName('24ES_comp_360x560.jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/24ES_comp_360x560.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('24ES_comp_360x560.jpg');
        $media->setFilename('24ES_comp_360x560.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        
        $galleryVertical->addImage($media);
        // dump('END LOAD');die;

        $media = new MediaObject();
        $media->setName('pic50.jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/pic50.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('pic50.jpg');
        $media->setFilename('pic50.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        
        $media = new MediaObject();
        $media->setName('20190919_150216.jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/20190919_150216.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('20190919_150216.jpg');
        $media->setFilename('20190919_150216.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);

        $media = new MediaObject();
        $media->setName('20190919_145742.jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/20190919_145742.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('20190919_145742.jpg');
        $media->setFilename('20190919_145742.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);

        $media = new MediaObject();
        $media->setName('20190919_150730(1).jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/20190919_150730(1).jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('20190919_150730(1).jpg');
        $media->setFilename('20190919_150730(1).jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);

        $media = new MediaObject();
        $media->setName('20190919_151858(1).jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/20190919_151858(1).jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('20190919_151858(1).jpg');
        $media->setFilename('20190919_151858(1).jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);

        $media = new MediaObject();
        $media->setName('20190919_152812(1).jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/20190919_152812(1).jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('20190919_152812(1).jpg');
        $media->setFilename('20190919_152812(1).jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);

        $media = new MediaObject();
        $media->setName('20190919_150828(1).jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/20190919_150828(1).jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('20190919_150828(1).jpg');
        $media->setFilename('20190919_150828(1).jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        
        $media = new MediaObject();
        $media->setName("L'agence de L'Immobilière d'Essaouira");
        $media->setUrl('https://www.youtube.com/watch?v=FvOCZqGuwC0');
        $media->setDimensions(null);
        $media->setOriginalFilename(null);
        $media->setFilename(null);
        $media->setEncodingFormat('video/youtube');
        $media->setContentSize(null);
        $manager->persist($media);


        $media = new MediaObject();
        $media->setName("Locations de villas en exclusivité sue golf avec L'Immobilière d'Essaouira");
        $media->setUrl('https://www.youtube.com/watch?v=4rZBFdnbMes');
        $media->setDimensions(null);
        $media->setOriginalFilename(null);
        $media->setFilename(null);
        $media->setEncodingFormat('video/youtube');
        $media->setContentSize(null);
        $manager->persist($media);
        


        $media = new MediaObject();
        $media->setName("Location d'une magnifique propriété vue sur mer en médina d'Essaouira");
        $media->setUrl('https://www.youtube.com/watch?v=y3eH0R6qPn8');
        $media->setDimensions(null);
        $media->setOriginalFilename(null);
        $media->setFilename(null);
        $media->setEncodingFormat('video/youtube');
        $media->setContentSize(null);
        $manager->persist($media);

        $media = new MediaObject();
        $media->setName('immobiliere-essaouira.jpg');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/immobiliere-essaouira.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('immobiliere-essaouira.jpg');
        $media->setFilename('immobiliere-essaouira.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
    }


    public function loadAccommodationsImages($manager) 
    {

        $pathFileAccomodationCsv = $this->container->get('kernel')
            ->getProjectDir() . '/accommodations_old.csv';
        $fileAccommodationsCsv = file_get_contents($pathFileAccomodationCsv);
        $fileAccommodations = $this->csvToArray->convert($fileAccommodationsCsv, ';');
        
        $filesystem = new Filesystem();
        $slugify = new Slugify();

        $i = 0; $batchSize = 20;
        $log = 'Start => ' . date('Y-m-d\ H:i:s.u');
        foreach($fileAccommodations as $accommodation) {
            $i++;

            dump('********** ' . $accommodation['TITRE_TEXTE'] . ' **********');
            dump(date('Y-m-d\ H:i:s.u') . ' : foreach media accommodation start ' . $i);
            
            $images = $accommodation['IMAGES'];
            $images = stripcslashes($images);
            $images = json_decode($images);
            $name = $slugify->slugify($accommodation['TITRE'] . '-' . $accommodation['REF']);

            
            if(null !== $images) {
                $gallery = new Gallery();
                $gallery->setName($name);
                $j = 0;
                foreach($images as $image) {
                    $j++;
                    $isConform = ($j <= 4)? true: false;
                    
                    // dump('***** ' . $image->name . ' *****');
                    // dump(date('Y-m-d\ H:i:s.u') . ' : foreach media accommodation foreach galerie');

                    $pathFileUpload = $this->container->get('kernel')
                        ->getProjectDir() . "/resources/immoe-upload/";
                    $pathFileTarget = $this->container->getParameter('assets.path') . DIRECTORY_SEPARATOR . $this->container->getParameter('assets.media.folder');
                    // $pathFileTargetLight = $this->container->get('kernel')
                    //     ->getProjectDir() . "/resources/immoe-upload-light/";
                    // if(!$filesystem->exists($pathFileTargetLight)) {
                    //     $filesystem->mkdir($pathFileTargetLight);
                    // }
                    $pathFileUpload = $pathFileUpload . $image->name;
                    $name = $slugify->slugify($image->description);
                    // $pathFileTargetLight = $pathFileTargetLight . $image->name;
                    $filename = $name . '__' .$image->name;
                    $pathFileTarget = $pathFileTarget . $filename;
                
                    if($filesystem->exists($pathFileUpload)) {

                        if(!$filesystem->exists($pathFileTarget)) {
                            $filesystem->copy($pathFileUpload, $pathFileTarget);
                            // $filesystem->copy($pathFileUpload, $pathFileTargetLight);
                        }
                        
                        $media = new MediaObject();
                        $media->setName($accommodation['IMAGE_desc']);
                        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/' . $filename);
                        $media->setDimensions([1920,1281]);
                        $media->setOriginalFilename($filename);
                        $media->setFilename($filename);
                        $media->setEncodingFormat('image/jpeg');
                        $media->setContentSize('image/jpeg');
                        $media->setIsConform($isConform);
                        $manager->persist($media);
                        // $manager->flush();
                        $gallery->addImage($media);

                        if (($j % $batchSize) === 0) {
                            $manager->flush();
                            $manager->clear();
                        }
                    }            
                }
                // $manager->flush();
                // $manager->clear();
            }
            dump(date('Y-m-d\ H:i:s.u') . ' : foreach media accommodation end');
            $manager->persist($gallery);
           
            // if (($i % $batchSize) === 0) {
                $manager->flush();
                // $manager->clear();
            // }

            // if($i > 10)
            //     break;

        }
        // $manager->flush();
        $manager->clear();

        $log = $log . ' - End => ' . date('Y-m-d\ H:i:s.u');
        dump($log);


    }

    public function loadDocuments($manager)
    {
        $document = new DocumentObject();
        $document->setName('Floor Plan Exemple');
        $document->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/document/files/floor_plans.png');
        $document->setDimensions([1920,1281]);
        $document->setOriginalFilename('floor-plans.png');
        $document->setFilename('floor-plans.png');
        $document->setEncodingFormat('image/png');
        $document->setContentSize('image/png');
        $documentType = $manager->getRepository(DocumentObjectType::class)
            ->findOneBy((['slug' => 'plan']));
        $document->setType($documentType);

        $manager->persist($document);
        $manager->flush();
    }



    public function loadVideos($manager)
    {
        $media = new MediaObject();
        $media->setName('Location villa Essaouira - Golf de Mogador - Maroc');
        $media->setUrl('https://www.youtube.com/watch?v=sNp0BhNX5Q4');
        $media->setDimensions(null);
        $media->setOriginalFilename(null);
        $media->setFilename(null);
        $media->setEncodingFormat('video/youtube');
        $media->setContentSize(null);
        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        $media->setName("L'Immobilière d'Essaouira  participe à l’émission La maison France 5");
        $media->setUrl('https://www.youtube.com/watch?v=EEAeWVjobZA');
        $media->setDimensions(null);
        $media->setOriginalFilename(null);
        $media->setFilename(null);
        $media->setEncodingFormat('video/youtube');
        $media->setContentSize(null);
        $manager->persist($media);
        $manager->flush();
    }

    public function loadRealEstateAgents($manager)
    {
        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/2ES_comp_562x562.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('2ES_comp_562x562.jpg');
        $media->setFilename('2ES_comp_562x562.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/4ES_comp_562x562.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('4ES_comp_562x562.jpg');
        $media->setFilename('4ES_comp_562x562.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/7ES_comp_562x562.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('7ES_comp_562x562.jpg');
        $media->setFilename('7ES_comp_562x562.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/9ES_comp_562x562.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('9ES_comp_562x562.jpg');
        $media->setFilename('9ES_comp_562x562.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();

    }

    public function loadLogos($manager) 
    {
        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/stamp.png');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('stamp.png');
        $media->setFilename('stamp.png');
        $media->setEncodingFormat('image/png');
        $media->setContentSize('image/png');

        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/logo_bg_primary.png');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('logo_bg_primary.png');
        $media->setFilename('logo_bg_primary.png');
        $media->setEncodingFormat('image/png');
        $media->setContentSize('image/png');

        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/logo_footer.png');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('logo_footer.png');
        $media->setFilename('logo_footer.png');
        $media->setEncodingFormat('image/png');
        $media->setContentSize('image/png');

        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/logo_header_background_dark.png');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('logo_header_background_dark.png');
        $media->setFilename('logo_header_background_dark.png');
        $media->setEncodingFormat('image/png');
        $media->setContentSize('image/png');

        $manager->persist($media);
        $manager->flush();
    }


    public function loadMedias($manager)
    {
        // About us
        $galleryHorizontal = new Gallery();
        $galleryHorizontal->setName('Demo galerie horizontal');
        $galleryVertical = new Gallery();
        $galleryVertical->setName('Demo galerie vertical');


        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/2ES_comp_562x562.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('2ES_comp_562x562.jpg');
        $media->setFilename('2ES_comp_562x562.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/4ES_comp_562x562.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('4ES_comp_562x562.jpg');
        $media->setFilename('4ES_comp_562x562.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/7ES_comp_562x562.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('7ES_comp_562x562.jpg');
        $media->setFilename('7ES_comp_562x562.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/9ES_comp_562x562.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('9ES_comp_562x562.jpg');
        $media->setFilename('9ES_comp_562x562.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();


        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/13ES_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('13ES_comp.jpg');
        $media->setFilename('13ES_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/13ES_comp_360x560.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('13ES_comp_360x560.jpg');
        $media->setFilename('13ES_comp_360x560.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        $manager->flush();
        $galleryAbout->addImage($media);

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/14ES_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('14ES_comp.jpg');
        $media->setFilename('14ES_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');
        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/14ES_comp_360x560.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('14ES_comp_360x560.jpg');
        $media->setFilename('14ES_comp_360x560.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();


        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/16ES_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('16ES_comp.jpg');
        $media->setFilename('16ES_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();


        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/16ES_comp_360x560.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('16ES_comp_360x560.jpg');
        $media->setFilename('16ES_comp_360x560.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();
        $galleryAbout->addImage($media);

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/28ES_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('28ES_comp.jpg');
        $media->setFilename('28ES_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();
        $galleryWhoWeAre->addImage($media);


        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/29ES_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('29ES_comp.jpg');
        $media->setFilename('29ES_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();
        $galleryWhoWeAre->addImage($media);


        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/30ES_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('30ES_comp.jpg');
        $media->setFilename('30ES_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();
        $galleryWhoWeAre->addImage($media);

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/31ES_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('31ES_comp.jpg');
        $media->setFilename('31ES_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();
        $galleryWhoWeAre->addImage($media);

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/33ES_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('33ES_comp.jpg');
        $media->setFilename('33ES_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/24ES_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('24ES_comp.jpg');
        $media->setFilename('24ES_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();

        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/24ES_comp_360x560.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('24ES_comp_360x560.jpg');
        $media->setFilename('24ES_comp_360x560.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();
        $galleryAbout->addImage($media);

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/pic1_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('pic1_comp.jpg');
        $media->setFilename('pic1_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/pic3_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('pic3_comp.jpg');
        $media->setFilename('pic3_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/pic4_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('pic4_comp.jpg');
        $media->setFilename('pic4_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/2819_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('2819_comp.jpg');
        $media->setFilename('2819_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/2817_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('2817_comp.jpg');
        $media->setFilename('2817_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/2816_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('2816_comp.jpg');
        $media->setFilename('2816_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();

        $media = new MediaObject();
        // $media->setName('');
        $media->setUrl('http://immobiliere-essaouira-cdn.graines-digitales.fr/uploads/media/files/2812_comp.jpg');
        $media->setDimensions([1920,1281]);
        $media->setOriginalFilename('2812_comp.jpg');
        $media->setFilename('2812_comp.jpg');
        $media->setEncodingFormat('image/jpeg');
        $media->setContentSize('image/jpeg');

        $manager->persist($media);
        $manager->flush();
        $galleryWhoWeAre->addImage($media);

        $manager->persist($galleryAbout);
        $manager->flush();

        $manager->persist($galleryWhoWeAre);
        $manager->flush();
    }


}
