<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;
use App\Entity\User;
use App\Entity\WebPage;
use App\Entity\Article;
use App\Entity\Tag;
use App\Entity\Organization;
use App\Entity\OrganizationType;
use App\Entity\MediaObject;
use App\Entity\DocumentObject;
use App\Entity\Gallery;
use App\Entity\WebPageTemplate;
use App\Entity\Accommodation;
use App\Entity\RealEstateAgent;
use App\Entity\AccommodationAmenity;
use App\Entity\AccommodationLabel;
use App\Entity\AccommodationNature;
use App\Entity\AccommodationPlace;
use App\Entity\AccommodationType;
use App\Entity\AccommodationLocation;
use App\Entity\RentalPriceType;
use App\Entity\RentalType;
use App\Entity\Component;
use App\Entity\Event;
use App\Entity\Rental;
use App\Entity\Person;
use Symfony\Component\EventDispatcher\EventDispatcher;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use App\Service\Export\CsvToArray;
use App\Service\Export\ArrayToCsv;
use Cocur\Slugify\Slugify;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\DBAL\DriverManager;


class WebPageFixtures extends AbstractFixtures
{
    private $csvToArray;
    private $arrayToCsv;
    private $references;

    public function __construct(CsvToArray $csvToArray, ArrayToCsv $arrayToCsv)
    {
        // ini_set('max_execution_time', 300);
        // ini_set('meemory_limit', 1024);

        $this->csvToArray = $csvToArray;
        $this->arrayToCsv = $arrayToCsv;
    }

    public function load(ObjectManager $manager)
    {   
        $this->loadAccommodations($manager);
        $this->reloadAccommodations($manager);
        $this->loadSocialLink($manager);
        $this->loadPagesAndArticles($manager);
        $this->loadEvents($manager);
        $this->reloadWebPagesAndArticles($manager);
    }

    public function loadAccommodations($manager)
    {
        $slugify = new Slugify();
        $dispatcher = new EventDispatcher();
        $faker = Faker\Factory::create('fr_FR');
        $seoMetaData = $this->container->get('app.meta_data');
        
        // méta données
        $agentStef = $manager->getRepository(
            RealEstateAgent::class
        )->findOneById(2);
        $agentGreg = $manager->getRepository(
            RealEstateAgent::class
        )->findOneById(4);
        $accommodationAmenities = $manager->getRepository(
            AccommodationAmenity::class
        )->findAll();
        $arrayAmenities = [];
        foreach ($accommodationAmenities as $key => $value) {
            $arrayAmenities[$value->getSlug()] = $value;
        }
        $accommodationLabels = $manager->getRepository(
            AccommodationLabel::class
        )->findAll();
        $arrayLabels = [];
        foreach ($accommodationLabels as $key => $value) {
            $arrayLabels[$value->getSlug()] = $value;
        }
        $accommodationNatures = $manager->getRepository(
            AccommodationNature::class
        )->findAll();
        $arrayNatures = [];
        foreach ($accommodationNatures as $key => $value) {
            $arrayNatures[$value->getSlug()] = $value;
        }
        $accommodationPlaces = $manager->getRepository(
            AccommodationPlace::class
        )->findAll();
        $arrayPlaces = [];
        foreach ($accommodationPlaces as $key => $value) {
            $arrayPlaces[$value->getSlug()] = $value;
        }
        $accommodationTypes = $manager->getRepository(
            AccommodationType::class
        )->findAll();
        $arrayTypes = [];
        foreach ($accommodationTypes as $key => $value) {
            $arrayTypes[$value->getSlug()] = $value;
        }
        $rentalPriceType = $manager->getRepository(
            RentalPriceType::class
        )->findAll();
        $arrayRentalPriceTypes = [];
        foreach ($rentalPriceType as $key => $value) {
            $arrayRentalPriceTypes[$value->getSlug()] = $value;
        }
        $accommodationLocation = $manager->getRepository(
            AccommodationLocation::class
        )->findAll();
        $arrayLocations = [];
        foreach ($accommodationLocation as $key => $value) {
            $arrayLocations[$value->getSlug()] = $value;
        }
        $tags = $manager->getRepository(
            Tag::class
        )->findAll();

        $medias = $manager->getRepository(
            MediaObject::class
        )->findAll();
        $arrayMedias = [];
        foreach ($medias as $key => $value) {
            $arrayMedias[$value->getId()] = $value;
        }

        $documents = $manager->getRepository(
            DocumentObject::class
        )->findAll();
        $arrayDocuments = [];
        foreach ($documents as $key => $value) {
            $arrayDocuments[$value->getId()] = $value;
        }

        $galleries = $manager->getRepository(
            Gallery::class
        )->findAll();
        $arrayGalleries = [];
        foreach ($galleries as $key => $value) {
            $arrayGalleries[$value->getSlug()] = $value;
        }
        $defaultGallery = $manager->getRepository(Gallery::class)->findOneById(3);

        /**
        * GET IMAGES
        **/
        $primaryImages = [11,12,13,14];
        $secondaryImages = [15,16,17,18];
        $galleries = [1,2];

        $pathFileAccommodationsCsv = $this->container->get('kernel')
            ->getProjectDir() . '/accommodations.csv';
        $fileAccommodationsCsv = file_get_contents($pathFileAccommodationsCsv);
        $fileAccommodations = $this->csvToArray->convert($fileAccommodationsCsv, ';');
        $pathFileUpload = $this->container->get('kernel')
                        ->getProjectDir() . "/resources/immoe-upload/";

        $i = 0; 
        $batchSize = 20;
        $fileNewAccommodations = [];
        $log = 'Start => ' . date('Y-m-d\ H:i:s.u');
        foreach($fileAccommodations as $accommodation) {
            if($i == 0) {
                $newKeys = array(
                    'new_reference (origine => REF)',
                    'new_surface_habitable (origine => SURFACE)',
                    'new_surface_terrain (origine => SURFACE)',
                    'new_nature (origine => TYPE)',
                    'new_lieux (origine => LIEUX)',
                    'new_type (origine => NOM_type)',
                    'new_duree_location (origine => UNITE)',
                    'new_type_prix_location (origine => UNITE)',
                    'new_commodites (fausses données)',
                    'new_label_mise_en_avant (fausses données)',
                    'new_agent_immobilier (fausses données)'
                );
                $fileNewAccommodations[0] = array_merge($newKeys, array_keys($accommodation));
            }
            $i++;
            

            dump('********** ' . $accommodation['TITRE_TEXTE'] . ' **********');
            dump(date('Y-m-d\ H:i:s.u') . ' : foreach accommodation start => n° ' . $i);  

            /**
            * base
            **/
            $entity = new Accommodation();
            // $entity->setName($faker->name);
            
            $entity->setNumberOfBathrooms(0);
            $entity->setMaximumOccupants(0);
            $entity->setNumberOfPieces(0);
            $entity->setAreaTerrace(0);
            $entity->setReference(null);
            $entity->setPushForward($accommodation['TITRE_TEXTE']);
            // $entity->setDescription($faker->text);
            $entity->setOldReference($accommodation['REF']);
            if('yes' == $accommodation['ACTIVE']) {
                $entity->setIsActive(true);
            } else {
                $entity->setIsActive(false);
            }
            
            /**
            * surface
            **/
            $nbOfRooms = (is_numeric($accommodation['new_chambres']))? $accommodation['new_chambres']: 0;
            $entity->setNumberOfRooms($nbOfRooms);

            $floorSize = ('\N' !== $accommodation['SURFACE']) ? $accommodation['SURFACE']: 0;
            $areaSize = 0;
            if(strpos($floorSize, "%")) {
                $tab = explode("%", $floorSize);
                $floorSize = $tab[1];
                $areaSize = $tab[0];
            }
            $entity->setFloorSize($floorSize);
            $entity->setAreaSize($areaSize);
            
            /**
            * geocoordinates
            **/
            $geo = $accommodation['map_longitude'] . ',' . $accommodation['map_laltitude'];
            $entity->setGeo($geo);
            /**
            * price
            **/
            $price = (is_numeric($accommodation['PRIX']))? $accommodation['PRIX']: 0;
            $entity->setPrice($price);
            /**
            * nature
            **/
            $mapNatures = array(
                'Vente' => 'vente',
                'Location' => 'location'
            );
            $slugNature = $mapNatures[$accommodation['TYPE']];
            $nature = $arrayNatures[$slugNature];
            $entity->setNature($nature);
            /**
            * place
            *
            **/    
            $mapPlaces = array(
                'campagne' => 'campagne',
                'golf-mogador' => 'golf-mogador',
                'medina' => 'medina',
                'nouvelle-ville' => 'nouvelle-ville',
                'Quartier des dunes' => 'nouvelle-ville',
                'Boulevard de la plage' => 'nouvelle-ville',
                'La lagune' => 'nouvelle-ville',
                'Quartier Azlef' => 'nouvelle-ville',
                'Diabet' => 'diabet',
                'Sidi Kaouki' => 'sidi-kaouki',
                'Douar Laarab' => 'douar-larab',
                'Ghazoua' => 'ghazoua',
                'Route de Marrakech' => 'route-de-marrakech',
                'Ida Ougourd' => 'ida-ougourd',
                'Hrarta' => 'hrarta',
                'Route de Safi' => 'route-de-safi',
                'Médina' => 'medina',
                'Nouvelle ville' => 'nouvelle-ville',
                'Plage dEssaouira' => 'nouvelle-ville',
                'Aéroport' => 'aeroport',
            );
            $slugPlace = 'nouvelle-ville';
            if(isset($mapPlaces[$accommodation['LIEUX']])) {
                $slugPlace = $mapPlaces[$accommodation['LIEUX']];
            }
            $place = $arrayPlaces[$slugPlace];
            $entity->setPlace($place);
            /**
            * TYPE
            */
            $mapTypes = array(
                'appartement' => 'appartement',
                'biens-de-prestige' => 'villa-golf',
                'fond-de-commerce' => 'affaires-commerciales',
                'golf-essaouira-mogador' => 'villa-golf',
                'maison' => 'maison-de-campagne',
                'riad' => 'riad',
                'gerance' => 'location-gerance',
                'louer-villas-golf-mogador' => 'villa-golf',
                'villa' => 'maison-de-campagne',
                'terrain' => 'terrain'
            );
            $slugType = 'appartement';
            if(isset($mapTypes[$accommodation['NOM_type']])) {
                $slugType = $mapTypes[$accommodation['NOM_type']];
            }
            $type = $arrayTypes[$slugType];
            $entity->setType($type);
            /**
            * location
            **/
            if('location' === $entity->getNature()->getSlug()) {
                
                if(!in_array(strtolower($accommodation['UNITE']), ['nuit', 'semaine'])) {
                    $slugDuration = 'longue-duree';
                    $slugRentalPriceType = 'au-mois';
                   
                } else {
                    $slugDuration = 'saisonniere';
                    $slugRentalPriceType = 'a-la-semaine';
                }

                $duration = $arrayLocations[$slugDuration];
                $entity->setDuration($duration);
               
                $rentalPriceType = $arrayRentalPriceTypes[$slugRentalPriceType];
                $entity->setRentalPriceType($rentalPriceType);
            }

            /**
            * FAKE DATA
            * AMENITIES, TAG, LABEL ...
            **/
            $newAmenities = [];
            $commodites = trim($accommodation['new_commodites']);
            if(!empty($commodites)) {
                $commodites = explode(",", $commodites);

                foreach ($commodites as $key => $value) {
                    $slug = $slugify->slugify($value);
                    if(isset($arrayAmenities[$slug])) {
                        $amenity = $arrayAmenities[$slug];
                         $entity->addAmenity($amenity);
                        $newAmenities[] = $amenity;
                    } 
                    // else {
                    //     $amenity = new AccommodationAmenity();
                    //     $amenity->setName($value);
                    //     $amenity->setWithPicto(false);
                    //     $manager->persist($amenity);
                    //     // $manager->flush(); 
                    // }

                   
                    
                }
            }

        

            // shuffle($accommodationAmenities);
            // $y = 0; $newAmenities = [];
            // foreach($accommodationAmenities as $amenity) {

            //   if(null != $amenity) {
            //     $entity->addAmenity($amenity);
            //     $newAmenities[] = $amenity;
            //   }

            //   if($y > 5) {
            //     break;
            //   }
            //   $y++;
            // }
            $newAmenities = implode(",", $newAmenities);

            // shuffle($tags);$y=0;$newTags = [];
            // foreach($tags as $tag) {
            //   if(null !== $tag) {
            //     $entity->addTag($tag);
            //     $newTags[] = $tag;
            //   }
            //   if($y > 2) {
            //     break;
            //   }
            //   $y++;
            // }
            // $newTags = implode(",", $newTags);
            // $fileNewAccommodations[$i]['new_tags_fake'] = $newTags;

            $label = $accommodationLabels[array_rand($accommodationLabels)];
            $entity->setLabel($label);


            /**
            * images, documents
            **/
            $nameGallery = $slugify->slugify($accommodation['TITRE'] . '-' . $accommodation['REF']);
            
            
            $gallery = $defaultGallery;
            $primaryImageId = array_rand($primaryImages);
            $primaryImage = $arrayMedias[$primaryImages[$primaryImageId]]; 
            if(isset($arrayGalleries[$nameGallery])) {
                $gallery = $arrayGalleries[$nameGallery];
                if(null !== $gallery && isset($gallery->getImages()[0])) {
                   $primaryImage = $gallery->getImages()[0];
                } 
            }
            $entity->setPrimaryImage($primaryImage);

            // $secondaryImage = null;
            // $secondaryImageId = array_rand($secondaryImages);
            // $offset = $secondaryImages[$secondaryImageId];
            // if(isset($arrayMedias[$offset])) {
            //     $secondaryImage = $arrayMedias[$secondaryImages[$secondaryImageId]];
            // }
            // $entity->setSecondaryImage($secondaryImage);

            // $video = null;
            // if(isset($arrayMedias[5])) {
            //     $video =  $arrayMedias[5];
            //     $entity->addVideo($video);    
            // }

            // $pdf = null;
            // if(isset($arrayDocuments[1])) {
            //     $pdf = $arrayDocuments[1];
            //     $entity->addPdf($pdf);
            // }

            $entity->setGallery($gallery);
            if('Grégory BOURGEAUX' === trim($accommodation['new_agent_immobilier'])) {
                $agent = $agentGreg;
            } else {
                $agent = $agentStef;
            }

            $entity->setRealEstateAgent($agent);
            dump(date('Y-m-d\ H:i:s.u') . ' : foreach accommodation before persist');
            
            /**
            * recording
            **/

            $manager->persist($entity);
            // if (($i % $batchSize) === 0) {
                // $manager->flush();
                // $manager->clear(); // Detaches all objects from Doctrine!
            // }
            // $manager->clear(Accommodation::class);
            // $manager->detach($entity);
            dump(date('Y-m-d\ H:i:s.u') . ' : foreach accommodation end');

            
            // dump($seoMetaData);
            $entity = $seoMetaData->process($entity);

            $reference = $this->createReference($entity);
            $entity->setReference($reference);

            /**
            * NEW ACCOMMODATIONS CSV
            **/
            $fileNewAccommodations[$i] = array_merge(
                array(
                    $entity->getReference()
                    , $floorSize
                    , $areaSize
                    , $nature->getName()
                    , $place->getName()
                    , $type->getName()
                    , $duration->getName()
                    , $rentalPriceType->getName()
                    , $newAmenities
                    , $label->getName()
                    , $agent->getPerson()->getFullName()
                )
                , $accommodation
            );
            
            /**
            * limitation
            **/
            // if($i > 10)
            //     break;
            
        }
        $log = $log . ' - End => ' . date('Y-m-d\ H:i:s.u');
        dump($log);
        dump(array_keys($this->references));

        $manager->flush();
        $manager->clear();
        
        $fileNewAccommodationsCsv = $this->arrayToCsv->convert($fileNewAccommodations);
        $pathFileNewAccommodationsCsv = $this->container->get('kernel')
            ->getProjectDir() . '/accommodations_new.csv';
        $filesystem = new Filesystem();
        $filesystem->dumpFile($pathFileNewAccommodationsCsv, $fileNewAccommodationsCsv);
    }

    private function reloadAccommodations($manager)
    {
        $config = new \Doctrine\DBAL\Configuration();
        $params = array(
            'url' => 'mysql://root:pass@johanrm@127.0.0.1:3306/import_platform',
        );
        $conn = DriverManager::getConnection($params, $config);
        $results = $conn->fetchAll(
            '
            SELECT REF, TEXTE_small, TEXTE_big 
            FROM `import_platform`.`old_immobiliere_essaouira_product_listing`
            '
        );

        $arrayTextes = [];
        foreach ($results as $key => $value) {
            $arrayTextes[$value['REF']]['ourOpinion'] = trim(
                strip_tags(
                    html_entity_decode($value['TEXTE_big'])
                )
            );
            $arrayTextes[$value['REF']]['description'] = trim(
                strip_tags(
                    html_entity_decode($value['TEXTE_small'])
                )
            );
        }
        

        $accommodations = $manager->getRepository(
            Accommodation::class
        )->findAll();
        $i = 0; 
        $batchSize = 20;
        foreach($accommodations as $key => $entity) {
            $i++;

            $k = $entity->getOldReference();
            if(isset($arrayTextes[$k])) {
                $entity->setOurOpinion($arrayTextes[$k]['ourOpinion']);
                $entity->setDescription($arrayTextes[$k]['description']);
            }
            
            dump('**********' . $entity->getMetaTitle() . '**********');
            dump(date('Y-m-d\ H:i:s.u') . ' : foreach accommodations entities start => n° ' . $i);
            
            $newGallery = $entity->getGallery(); 
            $images = [];
            $j = 0;
            foreach($newGallery->getImages() as $image) {
                $j++;
                // dump(date('Y-m-d\ H:i:s.u') . ' : foreach accommodations newgallery images start => n° ' . $j);

                $filename = $image->getFilename();
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $newFilename = $entity->getSlug() . '-' . $j . '.' . $ext;
                $image->setFilename($image->getOriginalFilename());
                $image->setName($entity->getSlug() . '-num-' . $j);
                $manager->persist($image);
                
            }

            $newGallery->setName($entity->getMetaTitle());
            $manager->persist($newGallery);
            dump(date('Y-m-d\ H:i:s.u') . ' : foreach accommodations entities end');

            
            $manager->persist($entity);
        }
       
        $manager->flush();
        $manager->clear();
    }

    private function reloadWebPagesAndArticles($manager)
    {
       
        $articles = $manager->getRepository(
            Article::class
        )->findAll();
        $seoMetaData = $this->container->get('app.meta_data');
        foreach($articles as $key => $entity) {
           

            $entity = $seoMetaData->process($entity);
            $manager->persist($entity);
        }
       
        $manager->flush();
        $manager->clear();

        $webPages = $manager->getRepository(
            WebPage::class
        )->findAll();
        $seoMetaData = $this->container->get('app.meta_data');
        foreach($webPages as $key => $entity) {
           

            $entity = $seoMetaData->process($entity);
            $manager->persist($entity);
        }
       
        $manager->flush();
        $manager->clear();
    }

    public function createReference($entity) 
    {
        $reference = null;
        $nature = $entity->getNature();
        $type = $entity->getType();
        if('vente' == $nature->getSlug()) {

            $initials = null;
            $initials.=  strtoupper(substr($nature->getSlug(), 0, 1));
            $words = explode("-", $type->getSlug());
            foreach($words as $word) {
                $initials.= strtoupper(substr($word, 0, 1));
            }
            $this->references[$initials][] = 'ref';
            $reference =  $initials . count($this->references[$initials]);
            
        } else if('location' == $nature->getSlug()) {

            $duration = $entity->getDuration();
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
            $this->references[$initials][] = 'ref';
            $reference =  $initials . count($this->references[$initials]);
        }

        return $reference;
    }

    private function loadSocialLink($manager)
    {

        $type = $manager->getRepository(OrganizationType::class)->findOneBy(array('slug' => 'lien-reseau-social'));

        $organization = new Organization();
        $organization->setName('Facebook');
        $organization->setUrl('https://web.facebook.com/ImmobilierEssaouira/');
        $organization->setType($type);

        $manager->persist($organization);
        $manager->flush();

        $organization = new Organization();
        $organization->setName('Youtube');
        $organization->setUrl('https://www.youtube.com/channel/UC_5LBYKZCyI3C54KmHjiTWQ');
        $organization->setType($type);

        $manager->persist($organization);
        $manager->flush();

        $organization = new Organization();
        $organization->setName('Instagram');
        $organization->setUrl('https://www.instagram.com/explore/locations/276862122394945/limmobiliere-dessaouira/');
        $organization->setType($type);

        $manager->persist($organization);
        $manager->flush();
    }

    private function loadPagesAndArticles($manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        /**
        * GET IMAGES HORIZONTAL
        **/
        $image1 = $manager->getRepository(MediaObject::class)->findOneById(11);
        $image2 = $manager->getRepository(MediaObject::class)->findOneById(12);
        $image3 = $manager->getRepository(MediaObject::class)->findOneById(13);
        $image4 = $manager->getRepository(MediaObject::class)->findOneById(14);
        
         /**
        * GET IMAGES VERTICAL
        **/
        $image5 = $manager->getRepository(MediaObject::class)->findOneById(15);
        $image6 = $manager->getRepository(MediaObject::class)->findOneById(16);
        $image7 = $manager->getRepository(MediaObject::class)->findOneById(17);
        $image8 = $manager->getRepository(MediaObject::class)->findOneById(18);
        
        $galleryHorizontal = $manager->getRepository(Gallery::class)->findOneById(1);
        $galleryVertical = $manager->getRepository(Gallery::class)->findOneById(2);


        /**
        * CREATE TAGS
        **/
        $tagAccueil = new Tag();
        $tagAccueil->setName('Accueil');
        $manager->persist($tagAccueil);
        $tagAgence = new Tag();
        $tagAgence->setName('L\'Agence');
        $manager->persist($tagAgence);
        $tagLocaux = new Tag();
        $tagLocaux->setName('Nos locaux');
        $manager->persist($tagLocaux);
        $tagNosServices = new Tag();
        $tagNosServices->setName('Nos services');
        $manager->persist($tagNosServices);
        $tagDecoration = new Tag();
        $tagDecoration->setName('Décoration Essaouira');
        $manager->persist($tagDecoration);
        $tagRenovation = new Tag();
        $tagRenovation->setName('Rénovation Essaouira');
        $manager->persist($tagRenovation);
        $tagConstruction = new Tag();
        $tagConstruction->setName('Construction Essaouira');
        $manager->persist($tagConstruction);
        $tagCreationPiscine = new Tag();
        $tagCreationPiscine->setName('Création de piscine');
        $manager->persist($tagCreationPiscine);
        $tagBlog = new Tag();
        $tagBlog->setName('Blog');
        $manager->persist($tagBlog);
        $tagLocation = new Tag();
        $tagLocation->setName('Location');
        $manager->persist($tagLocation);
        $tagVente = new Tag();
        $tagVente->setName('Vente');
        $manager->persist($tagVente);

        $manager->flush();

        $user = $manager->getRepository(User::class)->findOneById(6);
        /**************************************************************
        * ARTICLE : ACCUEIL
        ***************************************************************/
        $articleAgence = new Article();
        $articleAgence->setCategory($tagAgence);
        $articleAgence->addTag($tagAccueil);
        $articleAgence->addTag($tagAgence);
        $articleAgence->setHeadline(stripslashes('L\'Immobilière d\'Essaouira: l\'histoire d\'une passion'));
        $articleAgence->setAlternativeHeadline(stripslashes('L\'Immobilière d\'Essaouira'));
        $articleAgence->setPushForward('UN SAVOIR-ETRE ET UN SAVOIR-FAIRE RECONNUS');
        $articleAgence->setPrimaryImage($image1);
        $articleAgence->setSecondaryImage($image5);
        $articleAgence->setGallery($galleryHorizontal);
        $articleAgence->setUserCreated($user);
        $articleAgence->setArticleBody(html_entity_decode('<p>Notre histoire reste avant tout celle d&rsquo;une rencontre. Cet &eacute;v&egrave;nement a d&eacute;clench&eacute; la d&eacute;cision rapide d&rsquo;un nouveau projet<strong> </strong>de vie pour notre couple.</p>

<p>Notre passion commune pour le Maroc a permis de valider la destination o&ugrave; notre vie priv&eacute;e et professionnelle allait dor&eacute;navant se poursuivre, abandonnant nos carri&egrave;res de cadre bancaire et de charg&eacute;e de mission pour la Chambre de Commerce et d&rsquo;Industrie de la Rochelle, sans compter les ann&eacute;es pass&eacute;es dans le domaine de la d&eacute;coration.</p>

<p>Nous avons quitt&eacute; le confort douillet de l&rsquo;agglom&eacute;ration rochelaise pour changer de vie. Nous connaissions le Maroc et Essaouira. La ville nous a tr&egrave;s vite s&eacute;duit par son c&ocirc;t&eacute; authentique mais aussi par l&rsquo;accueil de ses habitants, sa notion de proximit&eacute;, sa m&eacute;dina c&oelig;ur historique de la ville sans voitures, son port, son climat. Que de ressemblances avec notre ville de d&eacute;part, La Rochelle. Les arguments n&rsquo;ont pas manqu&eacute; pour nous faire comprendre que nous &eacute;tions au bon endroit. Une fois install&eacute;s, l&agrave; encore, le hasard a jou&eacute; les bonnes f&eacute;es et g&eacute;n&eacute;r&eacute; rapidement les nombreuses activit&eacute;s professionnelles que nous exer&ccedil;ons encore aujourd&rsquo;hui avec tant de passion. Une nouvelle rencontre avec un entrepreneur marocain nous a permis de commencer la construction d&rsquo;un immeuble de 7 appartements. St&eacute;phane a donc embrass&eacute; la carri&egrave;re d&rsquo;entrepreneur en b&acirc;timent et surf&eacute; sur l&rsquo;engouement des europ&eacute;ens pour le Maroc. De nombreuses r&eacute;alisations ont &agrave; ce jour compl&eacute;t&eacute; notre carte de visite (r&eacute;novations diverses en m&eacute;dina et constructions de maisons en campagne, d&eacute;veloppement d&#39;un parc structur&eacute; de gestion locative, etc...).</p>

<p>D&egrave;s&nbsp; notre installation &agrave; Essaouira en 2004, nous avions &laquo; trac&eacute; la route &raquo;&hellip;</p>

<p>Aujourd&rsquo;hui, notre force r&eacute;side dans notre compl&eacute;mentarit&eacute;. St&eacute;phane s&rsquo;occupe des ventes de produits, de toutes les formalit&eacute;s administratives, des suivis de chantiers, etc&hellip; De mon c&ocirc;t&eacute;, j&rsquo;adapte au mieux les produits que nous vendons &agrave; Essaouira et sa r&eacute;gion (riads, villas, maisons de campagne, appartements, etc), en les am&eacute;nageant et les d&eacute;corant dans le but d&rsquo;optimiser leur rentabilit&eacute; locative. Notre souci premier reste de satisfaire notre client&egrave;le et de l&rsquo;accompagner dans ses projets d&rsquo;installation et de rentabilit&eacute; locative &agrave; Essaouira, dans les meilleures conditions.</p>

<p>Notre entreprise reste la structure id&eacute;ale sur laquelle les candidats &eacute;trangers &agrave; l&rsquo;accession &agrave; la propri&eacute;t&eacute; &agrave; Essaouira peuvent s&rsquo;appuyer. Pour la plus grande tranquillit&eacute; d&rsquo;esprit de nos clients, nous mettons tout en &oelig;uvre pour assurer personnellement le suivi de chaque affaire y compris les formalit&eacute;s administratives, sujet lourd mais n&eacute;anmoins incontournable.</p>

<p>Actuellement, notre parfaite connaissance du march&eacute; immobilier local en terme de transactions et de locations, coupl&eacute; &agrave; nos savoirs faire en mati&egrave;re d&rsquo;am&eacute;nagement et de d&eacute;coration, nous permet de p&eacute;renniser notre notori&eacute;t&eacute; et nos capacit&eacute;s &agrave; continuer d&rsquo;entreprendre au Maroc.</p>

<p>Nous restons &agrave; votre &eacute;coute au sein de notre&nbsp; agence.</p>

<p>A bient&ocirc;t.</p>

<p>Natacha SCHOPPE &amp; St&eacute;phane LAURENT</p>'));

        $articleAgence->setArticleResume(html_entity_decode(strip_tags('Notre histoire reste avant tout celle d&rsquo;une rencontre. Cet &eacute;v&egrave;nement a d&eacute;clench&eacute; la d&eacute;cision rapide d&rsquo;un nouveau projet<strong> </strong>de vie pour notre couple.Notre passion commune pour le Maroc a permis de valider la destination o&ugrave; notre vie priv&eacute;e et professionnelle allait dor&eacute;navant se poursuivre, abandonnant nos carri&egrave;res de cadre bancaire et de charg&eacute;e de mission pour la Chambre de Commerce et d&rsquo;Industrie de la Rochelle, sans compter les ann&eacute;es pass&eacute;es dans le domaine de la d&eacute;coration.')));
        $manager->persist($articleAgence);
        $manager->flush();

        /**************************************************************
        * ARTICLE : ACCUEIL
        ***************************************************************/
        $article = new Article();
        $article->setCategory($tagAgence);
        $article->addTag($tagAccueil);
        $article->setPrimaryImage($image2);
        $article->setSecondaryImage($image6);
        $article->setHeadline('Bienvenue');
        $article->setAlternativeHeadline('Bienvenue');
        $article->setPushForward(stripslashes('L\'Immobilière d\'Essaouira: l\'histoire d\'une passion'));
        $article->setGallery($galleryHorizontal);
        $article->setArticleBody("Contrairement à une opinion répandue, le Lorem Ipsum n'est pas simplement du texte aléatoire. Il trouve ses racines dans une oeuvre de la littérature latine classique datant de 45 av. J.-C., le rendant vieux de 2000 ans. Un professeur du Hampden-Sydney College, en Virginie, s'est intéressé à un des mots latins les plus obscurs, consectetur, extrait d'un passage du Lorem Ipsum, et en étudiant tous les usages de ce mot dans la littérature classique, découvrit la source incontestable du Lorem Ipsum. Il provient en fait des sections 1.10.32 et 1.10.33 du De Finibus Bonorum et Malorum (Des Suprêmes Biens et des Suprêmes Maux) de Cicéron. Cet ouvrage, très populaire pendant la Renaissance, est un traité sur la théorie de l'éthique. Les premières lignes du Lorem Ipsum");
        $article->setUserCreated($user);
        $manager->persist($article);
        $manager->flush();



        /**************************************************************
        * ARTICLE :  ACCUEIL
        ***************************************************************/
        $article = new Article();
        $article->setCategory($tagAgence);
        $article->addTag($tagAccueil);
        $article->setUserCreated($user);
        $article->setHeadline(stripslashes('Livre d\'or'));
        $article->setAlternativeHeadline(stripslashes('Livre d\'or'));
        $article->setPushForward('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
        $article->setPrimaryImage($image3);
        $article->setSecondaryImage($image7);
        $article->setArticleBody("Contrairement à une opinion répandue, le Lorem Ipsum n'est pas simplement du texte aléatoire. Il trouve ses racines dans une oeuvre de la littérature latine classique datant de 45 av. J.-C., le rendant vieux de 2000 ans. Un professeur du Hampden-Sydney College, en Virginie, s'est intéressé à un des mots latins les plus obscurs, consectetur, extrait d'un passage du Lorem Ipsum, et en étudiant tous les usages de ce mot dans la littérature classique, découvrit la source incontestable du Lorem Ipsum. Il provient en fait des sections 1.10.32 et 1.10.33 du De Finibus Bonorum et Malorum (Des Suprêmes Biens et des Suprêmes Maux) de Cicéron. Cet ouvrage, très populaire pendant la Renaissance, est un traité sur la théorie de l'éthique. Les premières lignes du Lorem Ipsum");
        $manager->persist($article);
        $manager->flush();


        /**************************************************************
        * ARTICLE :  ACCUEIL
        ***************************************************************/
        $article = new Article();
        $article->setCategory($tagAgence);
        $article->addTag($tagAccueil);
        $article->setUserCreated($user);
        $article->setPrimaryImage($image4);
        $article->setSecondaryImage($image8);
        $article->setHeadline('Informations légales');
        $article->setPushForward('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
        $article->setAlternativeHeadline('Informations légales');
        $article->setArticleBody("Contrairement à une opinion répandue, le Lorem Ipsum n'est pas simplement du texte aléatoire. Il trouve ses racines dans une oeuvre de la littérature latine classique datant de 45 av. J.-C., le rendant vieux de 2000 ans. Un professeur du Hampden-Sydney College, en Virginie, s'est intéressé à un des mots latins les plus obscurs, consectetur, extrait d'un passage du Lorem Ipsum, et en étudiant tous les usages de ce mot dans la littérature classique, découvrit la source incontestable du Lorem Ipsum. Il provient en fait des sections 1.10.32 et 1.10.33 du De Finibus Bonorum et Malorum (Des Suprêmes Biens et des Suprêmes Maux) de Cicéron. Cet ouvrage, très populaire pendant la Renaissance, est un traité sur la théorie de l'éthique. Les premières lignes du Lorem Ipsum");
        $manager->persist($article);
        $manager->flush();

        /**************************************************************
        * ARTICLE :  ACCUEIL
        ***************************************************************/
        $article = new Article();
        $article->setCategory($tagAgence);
        $article->addTag($tagAccueil);
        $article->setUserCreated($user);
        $article->setPrimaryImage($image2);
        $article->setSecondaryImage($image8);
        $article->setHeadline('Conseils immobilier');
        $article->setPushForward('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
        $article->setAlternativeHeadline('Conseils immobilier');
        $article->setArticleBody("Contrairement à une opinion répandue, le Lorem Ipsum n'est pas simplement du texte aléatoire. Il trouve ses racines dans une oeuvre de la littérature latine classique datant de 45 av. J.-C., le rendant vieux de 2000 ans. Un professeur du Hampden-Sydney College, en Virginie, s'est intéressé à un des mots latins les plus obscurs, consectetur, extrait d'un passage du Lorem Ipsum, et en étudiant tous les usages de ce mot dans la littérature classique, découvrit la source incontestable du Lorem Ipsum. Il provient en fait des sections 1.10.32 et 1.10.33 du De Finibus Bonorum et Malorum (Des Suprêmes Biens et des Suprêmes Maux) de Cicéron. Cet ouvrage, très populaire pendant la Renaissance, est un traité sur la théorie de l'éthique. Les premières lignes du Lorem Ipsum");

        $manager->persist($article);
        $manager->flush();

        /**************************************************************
        * ARTICLE :  NOS LOCAUX
        ***************************************************************/
        $article = new Article();
        $article->setCategory($tagLocaux);
        $article->addTag($tagAccueil);
        $article->setUserCreated($user);
        $article->setPrimaryImage($image2);
        $article->setSecondaryImage($image8);
        $video = $manager->getRepository(MediaObject::class)->findOneById(26);
        $article->addVideo($video);
        $article->setHeadline('L\'agence');
        $article->setPushForward('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
        $article->setAlternativeHeadline('Nos locaux');
        $article->setArticleBody("Notre agence se situe dans le nouveau quartier commercial d’Azlef, dans la nouvelle ville d'Essaouira, à quelques pas de la plage et de la médina.
Cette zone en fort développement économique abrite également des habitations récentes et de nombreux locaux commerciaux multi-activités.
Nous l’avons imaginée accueillante, basée sur un concept d’agencement unique et précurseur, et décorée  de façon moderne et design.
Notre objectif reste d'apporter à notre clientèle et nos prospects la garantie d'une qualité d’accueil et de service reconnus localement et basés sur l'engagement, l'efficacité et l'accompagnement.");
         $article->setArticleResume(html_entity_decode(strip_tags("Notre agence se situe dans le nouveau quartier commercial d’Azlef, dans la nouvelle ville d'Essaouira, à quelques pas de la plage et de la médina. Cette zone en fort développement économique abrite également des habitations récentes et de nombreux locaux commerciaux multi-activités. Nous l’avons imaginée accueillante, basée sur un concept d’agencement unique et précurseur, et décorée  de façon moderne et design. Notre objectif reste d'apporter à notre clientèle et nos prospects la garantie d'une qualité d’accueil et de service reconnus localement et basés sur l'engagement, l'efficacité et l'accompagnement.")));
        $article->setGallery($galleryHorizontal);
        $manager->persist($article);
        $manager->flush();



        /**************************************************************
        * ARTICLE :  NOS SERVICES
        ***************************************************************/
        $article = new Article();
        $article->setCategory($tagNosServices);
        $article->addTag($tagAccueil);
        $article->setUserCreated($user);
        $article->addTag($tagAgence);
        $article->addTag($tagConstruction);
        $article->addTag($tagNosServices);
        $imageConstruction1 = $manager->getRepository(MediaObject::class)->findOneById(20);
        $imageConstruction2 = $manager->getRepository(MediaObject::class)->findOneById(22);
        $imageConstruction3 = $manager->getRepository(MediaObject::class)->findOneById(24);
        $imageConstruction4 = $manager->getRepository(MediaObject::class)->findOneById(25);
        

        $galleryConstruction = new Gallery();
        $galleryConstruction->setName('Construction Essaouira');
        $galleryConstruction->addImage($imageConstruction1);
        $galleryConstruction->addImage($imageConstruction2);
        $galleryConstruction->addImage($imageConstruction3);
        $galleryConstruction->addImage($imageConstruction4);

        $article->setPrimaryImage($imageConstruction1);
        
        
        $article->setSecondaryImage($image7);
        $article->setHeadline('Construction Essaouira');
        $article->setPushForward('Profitez de notre expérience depuis 2004');
        $article->setAlternativeHeadline('Construction Essaouira');
        $article->setArticleBody("L'Immobilière d'Essaouira vous propose ses services en matière de construction immobilière et met à votre disposition son expérience. Nous entreprenons et coordonnons pour vous et en votre absence la  réalisation et le suivi de vos travaux, en respectant le cahier des  charges défini ensemble et en assurant des comptes rendus réguliers de  l’avancement de votre chantier par e-mail accompagnés de photos et  commentaires. Ces prestations sont assurées par Natastef Maroc SARL, notre  société d’entreprise du bâtiment. Nous vous invitons à visiter en images  quelques une de nos réalisations  afin de juger de notre  sérieux  et  de la qualité de  nos prestations. A ce jour, nous sommes en mesure d’intervenir dans la bonne réalisation  et l’exécution minutieuse de chaque étape technique: conception  architecturale et définition précise de votre projet, coordination  technique, choix des matériaux et des procédés constructifs adaptés à  chaque projet, en campagne ou en ville.");
        $article->setBlockQuoteTitle($faker->title);
        $article->setBlockQuote($faker->text);
        // $article->setArticleResume(' ');
        $article->setText(html_entity_decode(strip_tags('Notre position et notre connaissance du marché local ainsi que la qualité des relations que nous entretenons avec l’ensemble de nos partenaires locaux favorisent la négociation des achats de prestations (interventions possibles de sociétés spécialisés dans certains domaines spécifiques) et de matériaux aux meilleurs prix.  Nous nous engageons à optimiser la mise en valeur de votre bien par l’utilisation de matériaux traditionnels marocains et en collaboration avec les meilleurs maîtres artisans de la région.Sur ce point, notre savoir faire de constructeurs prend la forme d’un engagement constant : optimiser les coûts, tenir les délais,  accroitre la fiabilité de nos constructions grâce à un contrôle très stricte et une présence quotidienne.Notre savoir faire s’exprime enfin à travers un accompagnement attentif et rigoureux : des  démarches administratives jusqu’au stade des finitions décoratives.')));
        $manager->persist($article);
        $manager->persist($galleryConstruction);
        $manager->flush();

        $article = new Article();
        $article->setCategory($tagConstruction);
        $article->addTag($tagAccueil);
        $article->setUserCreated($user);
        $article->addTag($tagAgence);
        // $article->addTag($tagNosServices);
        $article->setPrimaryImage($image1);
        $article->setSecondaryImage($image7);
        $article->setGallery($galleryHorizontal);
        $article->setHeadline(stripslashes('Construction d\'une villa de standing à Essaouira'));
        $article->setPushForward(stripslashes('Construction d\'une villa de standing'));
        $article->setAlternativeHeadline('Construction Essaouira');
        $article->setArticleBody("Construction Essaouira: Une fabuleuse villa au coeur des paysages uniques de la région d'Essaouira construite et décorée par l'Immobilière d'Essaouira ayant fait l'objet d'un reportage télévisé le 9/11/2011 dans l'émission LA MAISON FRANCE 5 avec Stéphane THEBAUT.

Près de 3 ans de construction depuis la pose de la première pierre et jusqu’à l’installation de l’ameublement et de la décoration.
Demeure de 630 m² imaginée et dessinée par Natacha SCHOPPE et Stéphane LAURENT, intégralement bâtie en pierres de pays taillées, alliant tradition et modernité.
15 ouvriers présents au quotidien durant 3 ans.
Sélection des meilleurs matériaux pour un résultat à la hauteur de nos exigences en matière de confort et de fiabilité.
Décoration chic et soignée.
Notre plus belle réalisation à Essaouira en termes de construction et de décoration.");
        $manager->persist($article);
        $manager->flush();

        /**************************************************************
        * ARTICLE :  AVIS
        ***************************************************************/
        $article = new Article();
        $article->setCategory($tagBlog);
        $article->addTag($tagAgence);
        $article->setUserCreated($user);
        $article->addTag($tagAccueil);
        $article->setPrimaryImage($image3);
        $article->setSecondaryImage($image8);
        $article->setHeadline('Avis client');
        $article->setHeadline('Avis clients');
        $article->setPushForward('');
        $article->setAlternativeHeadline('');
        $article->setArticleBody("Créée en 2004, notre agence jouit d’une notoriété importante auprès de ses clients, partenaires et fournisseurs. Découvrez quelques avis laissés par notre clientèle sur les services de l’agence.");
        $article->setArticleResume("https://www.google.com/search?client=ubuntu&channel=fs&q=immobiliere%20essaouira&ie=utf-8&oe=utf-8&hs=oKf&sxsrf=ACYBGNRTwLlYuLFJFYTP_Sj7gcfrR6-3RA:1571999102059&npsic=0&rflfq=1&rlha=0&rllag=31509890,-9760212,668&tbm=lcl&rldimm=16633084754980536288&ved=2ahUKEwjs4MKAmbflAhWjyoUKHbDzAE4QvS4wAXoECAYQIA&rldoc=1&tbs=lrf:!3sIAE,lf:1,lf_ui:2&rlst=f#lrd=0xdad9a486d21cf41:0xe6d4967573947fe0,1,,,&rldoc=1");
        $manager->persist($article);
        $manager->flush();

        /**************************************************************
        * ARTICLE :  NOS SERVICES
        ***************************************************************/
        $article = new Article();
        $article->setCategory($tagNosServices);
        $article->addTag($tagAccueil);
        $article->addTag($tagAgence);
        $article->setUserCreated($user);
        $article->addTag($tagRenovation);
        $article->addTag($tagNosServices);
        $article->setPrimaryImage($image3);
        $article->setSecondaryImage($image5);
        $component = $manager->getRepository(Component::class)->findOneById(3);
        $article->addComponent($component);
        $article->setGallery($galleryHorizontal);
        $article->setHeadline('Rénovation Essaouira');
        $article->setPushForward('Tous travaux');
        $article->setAlternativeHeadline('Rénovation Essaouira');
        $article->setArticleBody("L'Immobilière d'Essaouira est une agence spécialisée dans la rénovation de riads, de villas et d'appartements à Essouira et dans toute sa région. Nous avons à ce jour pu exercer notre expérience en matière de rénovation sur de nombreux biens existants  à Essaouira : ancienne ville (médina), nouvelle ville ou campagne. De nombreuses réalisations on fait objet de restructuration amenant un meilleur confort de vie, des espaces plus volumineux, création de pièces supplémentaires,  de terrasses ou patios fermés et /ou ouverts, création d’espaces extérieurs, réhabilitation d’escaliers, etc… Nous nous appliquons à sélectionner et à combiner intelligemment les matériaux, les équipements, et les techniques constructives qui répondront à vos attentes tout en préservant vos goûts et le confort souhaité.");
        $article->setText($faker->text);
        $article->setBlockQuoteTitle($faker->title);
        $article->setBlockQuote($faker->text);
        $manager->persist($article);
        $manager->flush();

        $article = new Article();
        $article->setCategory($tagRenovation);
        $article->addTag($tagAccueil);
        $article->addTag($tagAgence);
        $article->setUserCreated($user);
        // $article->addTag($tagNosServices);
        $article->setPrimaryImage($image3);
        $article->setSecondaryImage($image5);
        $article->setGallery($galleryHorizontal);
        $article->setHeadline('Dar Nouchka');
        $article->setPushForward('Dar Nouchka');
        $article->setAlternativeHeadline('Rénovation Essaouira');
        $article->setArticleBody("Rénovation Essaouira: Reconstruction d'une maison dans la médina d'Essaouira. Démolition d'une maison en médina, création de nouvelles dalles, agencement des pièces de vie, de 4 chambres et de 4 salles de bains. Construction d'une terrasse et d'un solarium avec vue panoramique sur mer");
        $manager->persist($article);
        $manager->flush();

        $article = new Article();
        $article->setCategory($tagRenovation);
        $article->addTag($tagAccueil);
        $article->addTag($tagAgence);
        $article->setUserCreated($user);
        // $article->addTag($tagNosServices);
        $video = $manager->getRepository(MediaObject::class)->findOneById(6);
        $article->addVideo($video);
        $article->setPrimaryImage($image3);
        $article->setSecondaryImage($image5);
        $article->setGallery($galleryHorizontal);
        $article->setText($faker->text);
        $article->setHeadline('Dar Ghazoua');
        $article->setPushForward('Dar Ghazoua');
        $article->setAlternativeHeadline('Rénovation Essaouira');
        $article->setArticleBody("Rénovation Essaouira: rénovation d'une villa à Essaouira par l'Immobilière d'Essaouira

Maison entièrement rénovée en 2006. Réhabilitation du jardin (avec création d’espace, de toilettes extérieur, etc..). Réfection des espaces intérieurs, création d’une cheminée, d’un salon en superposition, d’une cuisine ouverte et d’un patio vitré pour plus de clarté. Réfection de la salle de bain et création d’un toilette séparé. Garage aménagé avec point d’eau.
Elaboration de terrasses étanches et carrelées, création d’un sas d’entrée avec rangement.
Changement intégrale du circuit de plomberie et d’électricité.
L’utilisation de matériaux nobles tels que tadelakdt, pierre de pays, terre cuite au sol, carreaux ciments traditionnels, ont su offrir à cette maison une nouvelle vie tout en respectant une esthétique parfaite et en sachant optimiser ses espaces de vie intérieurs et extérieurs.
Très belle réalisation et confort absolu.");
        $manager->persist($article);
        $manager->flush();

        /**************************************************************
        * ARTICLE :  NOS SERVICES
        ***************************************************************/
        $article = new Article();
        $article->setCategory($tagNosServices);
        $article->addTag($tagAccueil);
        $article->addTag($tagAgence);
        $article->setUserCreated($user);
        $article->addTag($tagDecoration);
        $article->addTag($tagNosServices);
        $video = $manager->getRepository(MediaObject::class)->findOneById(6);
        $article->addVideo($video);
        $component = $manager->getRepository(Component::class)->findOneById(3);
        $article->addComponent($component);
        $image = $manager->getRepository(MediaObject::class)->findOneById(23);
        $article->setPrimaryImage($image);
        $article->setSecondaryImage($image5);
        $article->setText($faker->text);
        $article->setHeadline('Décoration Essaouira');
        $article->setAlternativeHeadline('Décoration Essaouira');
        $article->setPushForward('Services');
        $article->setArticleBody(stripslashes('Pour tous vos travaux de décoration de vos villas, riads et appartements, l\'Immobilière d\'Essaouira met à votre disposition son expérience dans toute la région. Nous vous proposons également nos conseils en matière d’aménagement, de décoration et d’équipement d’intérieur. Votre intérieur est le reflet de votre personnalité et de votre mode de vie. Il doit donc être adapté à votre goût et à vos besoins. En respectant vos envies, vos ambiances et votre budget, nous créerons, l’espace où vous aimerez vivre et recevoir. Lors d’un entretien personnalisé, vous nous ferez  part de vos goûts et de vos besoins afin que nous vous proposions des solutions originales, adaptées et astucieuses pour renouveler la décoration de votre appartement ou votre maison.'));
        $article->setBlockQuoteTitle($faker->title);
        $article->setBlockQuote($faker->text);
        $article->setText(html_entity_decode(strip_tags('Notre connaissance des matériaux et des besoins recherchés nous permet de vous proposer des prestations réfléchies en termes de confort et de fiabilité, alliant l’esprit décoratif local au confort moderne de notre civilisation occidentale. En matière de décoration, le Maroc dispose d’un choix varié de produits. Nous travaillons avec les meilleurs artisans d’Essaouira et de Marrakech afin d’apporter de nouvelles tendances, de nouvelles idées dans le plus pur respect des traditions et matériaux  afin de sublimer  vos intérieurs. Se sentir chez soi, améliorer son confort de vie, aspirer à d’autres horizons, tout est mis en œuvre pour permettre à notre clientèle de commencer une nouvelle vie, ailleurs et différente….')));
        $manager->persist($article);
        $manager->flush();


        $article = new Article();
        $article->setCategory($tagDecoration);
        $article->addTag($tagAccueil);
        $article->addTag($tagAgence);
        $article->setUserCreated($user);
        $article->addTag($tagDecoration);
        $article->setPrimaryImage($image4);
        $article->setSecondaryImage($image5);
        $article->setGallery($galleryHorizontal);
        $article->setHeadline(stripslashes('Transformation d\'un appartement typique marocain en un logement bien pensé et agréable à vivre'));
        $article->setAlternativeHeadline('Réalisation 2016');
        $article->setPushForward('Home staging d\'un appartement');
        $article->setArticleBody(stripslashes("Objectifs des propriétaires: 

Optmiser leur patrimoine constitué d'appartements peu adaptés aux attentes d'une clientèle de locataires européens et recherche de rentabiité locative.



Les conseils de l'Immobilière d'Essaouira:

Abattre les cloisons d'une cuisine bloquant l'entrée de lumière au Sud.

Créer un open-space avec bar et encastrement des équipements de cuisine.

Remplacer les faiences murales et du plafond de la cuisine par de la peinture blanche.

Remplacer l'ensemble des huisseries bois par du PVC double vitrage et volets roulants électriques.

Création d'un dressing en dur dans l'une des chambres.

Changement des faïences de sol.

Réhabilitation de la salle de bain avec création d'une douche à l'italienne.

Aménagements en mobilier à tendance moderne.

"));

        $manager->persist($article);
        $manager->flush();

        $article = new Article();
        $article->setCategory($tagDecoration);
        $article->addTag($tagAccueil);
        $article->addTag($tagAgence);
        $article->setUserCreated($user);
        $article->addTag($tagDecoration);
        $article->setPrimaryImage($image4);
        $article->setSecondaryImage($image5);
        $article->setGallery($galleryHorizontal);
        $article->setText($faker->text);
        $article->setBlockQuoteTitle($faker->title);
        $article->setBlockQuote($faker->text);
        $video = $manager->getRepository(MediaObject::class)->findOneById(6);
        $article->addVideo($video);
        $article->setHeadline('Décoration d\'une maison de charme');
        $article->setAlternativeHeadline('Décoration Essaouira');
        $article->setPushForward(stripslashes('Décoration d\'une maison de charme'));
        $article->setArticleBody(stripslashes("Décoration Essaouira: Très belle maison décorée par l'Immobilière d'Essaouira ayant fait l'objet d'une parution dans la revue de décoration et d'architecture n°91 de Mai 2011 de \"Maisons du Maroc\" et d'un reportage télévisé le 9 novembre 2011 dans le magazine hebdomadaire \"La Maison France 5\" présenté par Stéphane Thébaut.

Décoration Essaouira
Plusieurs mois de recherches pour une décoration soignée, chic et non ostentatoire.
Une grande partie de nos meubles ont été dessinés et créés sur mesure compte tenu des surfaces et de l’espace des pièces. Les autres ont été chinés.
Des patines « maison » ont été essayées et des croquis ont été réalisés pour optimiser les espaces. L’intervention de certains créateurs de Marrakech a permis d’assurer une décoration unique.
Les pierres intérieures ont été traitées et peintes de couleur crème afin d’en  faciliter l’entretien et d’obtenir un rendu propre et homogène. Tous les plafonds ont été peints et traités, poutres apparentes, rondins de thuya traditionnels ou lattes de bois.
Mélange des bois, mélange des styles, mélanges des couleurs, tendances claires ou foncées selon les pièces pour créer des ambiances différentes.
Un salon aux meubles venus d’Afrique donnant un confort cosy, une salle à manger berbère avec ses meubles chinés et anciens, une cuisine de type provençale très fonctionnelle ou chaque espace reste optimisé, la terrasse couverte aux meubles robustes (mélange de pierres massives et de bois en blocs).
Toutes les chambres abordent un thème différent :
- suite parentale avec poêle à bois, parquet peint, lit king size, meubles indonésiens et équipement moderne, tadelakdt crème, plafond en bois peint, lustre design
- chambre enfant naviguant entre L’île de Ré et l’île de Mogador par sa couleur de tadelakt gratté bleu pétrole et crème, plafond de bois peint et sol en terre cuite.
- chambre d’invités romantique, plafond de bois peint parme et crème, tadelakdt crème et décoration noire et silver.
- studio plus marocain avec meubles en fer de créateur, tendance crème et chocolat, fer forgé créé à la demande, tissus assortis. Sols en terre et carreaux ciment. Salle de bain tadelakt chocolat à l’aspect chic et gourmand.
- chambre plus contemporaine, crème et fauve, plafond peint et sol en terre.
- salle de jeu gris/bleu avec sol en carreaux ciment, formant un tapis de couleurs claires. Plafonds en bois peint.
Chaque élément décoratif de cette maison en font un lieu unique, chaque objet, chiné ou créé prend sa place, cette maison reste notre plus belle réalisation côté déco. Quelques photographes d’ici et d’ailleurs ont d’ailleurs shooté le lieu pour en faire prochainement quelques reportages dans les plus prestigieux magasines de décoration."));

        $manager->persist($article);
        $manager->flush();

        $article = new Article();
        $article->setCategory($tagDecoration);
        $article->addTag($tagAccueil);
        $article->addTag($tagAgence);
        $article->setUserCreated($user);
        $article->addTag($tagDecoration);
        $article->setPrimaryImage($image4);
        $article->setSecondaryImage($image5);
        $article->setGallery($galleryHorizontal);
        $article->setText($faker->text);
        $article->setBlockQuoteTitle($faker->title);
        $article->setBlockQuote($faker->text);
        $video = $manager->getRepository(MediaObject::class)->findOneById(6);
        $article->addVideo($video);
        $article->setHeadline('Décoration d\'une maison à Ghazoua');
        $article->setAlternativeHeadline('Décoration Essaouira');
        $article->setPushForward(stripslashes('Décoration d\'une maison à Ghazoua'));
        $article->setArticleBody(stripslashes("Maison entièrement rénovée et décorée en 2006 par nos soins. La décoration de ce charmant pied à terre s'est voulue 'chic-beldi' afin de faire de ce petit volume un nid douillet et très agréable à vivre."));

        $manager->persist($article);
        $manager->flush();


        

        /**************************************************************
        * ARTICLE :  NOS SERVICES
        ***************************************************************/
        $article = new Article();
        $article->setCategory($tagNosServices);
        $article->addTag($tagAgence);
        $article->setUserCreated($user);
        $article->addTag($tagAccueil);
        $article->addTag($tagCreationPiscine);
        $article->addTag($tagNosServices);
        $article->setPrimaryImage($image3);
        $article->setSecondaryImage($image8);
        $article->setHeadline('Création de piscine');
        $article->setText($faker->text);
        $article->setBlockQuoteTitle($faker->title);
        $article->setBlockQuote($faker->text);
        $component = $manager->getRepository(Component::class)->findOneById(3);
        $article->addComponent($component);
        $article->setPushForward('Services');
        $article->setAlternativeHeadline('Création de piscine');
        $article->setArticleBody("Nous avons réalisé plusieurs projets de piscines à Essaouira et sa région: piscines à skimmers, à débordement, bassins de nage.... Nos réalisations font l'objet d'un respect à la lettre du cahier des charges: béton armé et vibré pour les voiles, pose de revêtements traditionnels (zelliges, tadelakt) ou plus modernes (Mortex, Diamond Brite, etc). La pose des circuits hydrauliques est réalisée par des techniciens expérimentés. Nous travaillons avec des prestataires spécialisés s'agissant de pose de matériel de traitement d'eau spécifique (pompe à vitesse variable, traitement au sel, régulation automatique de PH).");
        $video = $manager->getRepository(MediaObject::class)->findOneById(6);
        $article->addVideo($video);
        $manager->persist($article);
        $manager->flush();

        /**************************************************************
        * ARTICLE :  BLOG
        ***************************************************************/
        $article = new Article();
        $article->setCategory($tagBlog);
        $article->addTag($tagAgence);
        $article->setUserCreated($user);
        $article->addTag($tagAccueil);
        // $article->addTag($tagNosServices);
        $article->setPrimaryImage($image3);
        $article->setSecondaryImage($image8);
        $article->setHeadline(stripslashes('L\'immobilière d’Essaouira mise à l\'honneur à la télévision française'));
        $article->setPushForward('Reportage La Maison France 5 (09/11/2011)');
        $article->setAlternativeHeadline(stripslashes('L\'Immobilière d’Essaouira dans un article du New-York Times'));
        $article->setArticleBody(stripslashes("Diffusée toutes les semaines sur la chaine de télévision française France 5, elle décrypte  les dernières tendances déco, présente les nouveautés en termes de technique BTP et fait découvrir et visiter au téléspectateur des habitations hors du commun.
Le programme s’articule autour d’un fil conducteur qui est la visite d’un lieu : demeure d’exception, quartier mythique ou ville emblématique.
Cette semaine dans l’émission diffusée le mercredi 09 Novembre 2011, c’est la ville d’Essaouira au Maroc qui est mise à l’honneur.
Entrecoupée de différents sujets sur les tendances déco ou l’aménagement intérieur, l’émission sillonne la cité des alizés pour présenter différentes facettes de la tradition artisanale marocaine avec le travail du bois de thuya ou la tradition des riads et de leurs patios.
Enfin le programme se conclut par la découverte d’une magnifique propriété, et la visite guidée des lieux par Natacha SCHOPPE, co-fondatrice avec Stéphane LAURENT de l'Immobilière d'Essaouira. Au cœur de la campagne souirie, bâtie sur un champ d’arganiers de 2 hectares, la demeure, inspirée des haciendas espagnoles, est un exemple de l’alliance parfaite des matériaux ancestraux à une décoration moderne et contemporaine. Conception et réalisation par l'Immobilière d'Essaouira."));
        $video = $manager->getRepository(MediaObject::class)->findOneById(6);
        $article->addVideo($video);
        
        $manager->persist($article);
        $manager->flush();

        $article = new Article();
        $article->setCategory($tagBlog);
        $article->addTag($tagAgence);
        $article->setUserCreated($user);
        $article->addTag($tagAccueil);
        // $article->addTag($tagNosServices);
        $article->setPrimaryImage($image3);
        $article->setSecondaryImage($image8);
        $article->setHeadline('L\'Immobilière d’Essaouira en parution dans un article du New-York Times');
        $article->setPushForward('Reportage « The New-Yok Times » (23/11/2016)');
        $article->setAlternativeHeadline('L\'Immobilière d’Essaouira en parution dans le New-York Times');
        $article->setArticleBody("Natacha, directrice de L'Immobilière d'Essaouira, a participé le 23-11-2016 à un reportage photos réalisé pour la promotion de la vente d'un bien immobilier de prestige sélectionné par le NEW-YORK TIMES.
L'ensemble des clichés est l'oeuvre de la photographe Ingrid PULLAR.
A découvrir ici : (https://www.nytimes.com/2016/11/23/realestate/real-estate-in-morocco.html?action=click&contentCollection=Real");
        $manager->persist($article);
        $manager->flush();

        $article = new Article();
        $article->setCategory($tagBlog);
        $article->addTag($tagAgence);
        $article->setUserCreated($user);
        $article->addTag($tagAccueil);
        // $article->addTag($tagNosServices);
        $article->setPrimaryImage($image3);
        $article->setSecondaryImage($image8);
        $article->setHeadline('Reportage dans le magasine Hikayats');
        $article->setPushForward('Reportage dans le magasine Hikayats');
        $article->setAlternativeHeadline('Reportage dans le magasine Hikayats');
        $article->setArticleBody(stripslashes(html_entity_decode("HIKAYATS ESSAOUIRA, premier magazine du nom sorti en octobre 2015 compile quelques unes des plus belles histoires de la ville. 
Il vous est présenté en ligne et en version franco-anglaise en cliquant ici (https://issuu.com/lecafedessports/docs/es1_magazine) L'Immobilière d'Essaouira a été sollicitée pour un reportage de 6 pages intitulé \"La maison sur la colline\" (pages 60 à 65). Disponible et gratuit dans votre agence. Bonne lecture à tous!")));
        $manager->persist($article);
        $manager->flush();
        

       


        /**************************************************************
        * WEBPAGES :  CONTACT
        ***************************************************************/
        $template = $manager->getRepository(WebPageTemplate::class)->findOneBy(array('slug' => 'contact'));
        $page = new WebPage();
        $page->setWebPageTemplate($template);
        $page->setHeadline('Contact');
        $page->setPushForward('Pour tous renseignements contactez l\'Immobilière d\'Essaouira');
        $page->setArticle($article);
        $page->setAlternativeHeadline('Contact');
        $page->setCategory($tagAgence);
        $page->addTag($tagAgence);
        $page->setPrimaryImage($image1);
        $page->setSecondaryImage($image8);
        $manager->persist($page);
        $manager->flush();

        /**************************************************************
        * WEBPAGES :  ACCUEIL
        ***************************************************************/
       $template = $manager->getRepository(WebPageTemplate::class)->findOneBy(array('slug' => 'home'));
        $page = new WebPage();
        $page->setWebPageTemplate($template);
        $page->setHeadline('Accueil');
        $page->setAlternativeHeadline('Accueil');
        $page->setText(stripslashes(html_entity_decode('Notre agence se situe dans le nouveau quartier commercial d’Azlef, dans la nouvelle ville d\'Essaouira, à quelques pas de la plage et de la médina. Cette zone en fort développement économique abrite également des habitations récentes et de nombreux locaux commerciaux multi-activités.
Nous l’avons imaginée accueillante, basée sur un concept d\’agencement unique et précurseur, et décorée  de façon moderne et désign. Notre objectif reste d\'apporter à notre clientèle et nos prospects la garantie d\'une qualité de service reconnue localement et basée sur l\'engagement, l\'efficacité, l\'accompagnement et la notion de service.')));
        $page->setPushForward('Pour tous renseignements contactez l\'Immobilière d\'Essaouira');
        $page->setArticle($articleAgence);

        $page->setGallery($galleryHorizontal);
        $page->setCategory($tagAgence);
        $page->addTag($tagAgence);
        $page->setPrimaryImage($image3);
        $page->setSecondaryImage($image5);
        $manager->persist($page);
        $manager->flush();


        /**************************************************************
        * WEBPAGES :  L AGENCE
        ***************************************************************/
        $template = $manager->getRepository(WebPageTemplate::class)->findOneBy(array('slug' => 'about-us'));
        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setWebPageTemplate($template);
        $page->setHeadline('L\'agence');
        $page->setGallery($galleryVertical);
        $page->setPushForward("L'immobilière d'essaouira: l'histoire d'une rencontre");
        $page->setAlternativeHeadline('L\'agence');
        $page->setText(stripslashes(html_entity_decode("Notre histoire reste avant tout celle d’une rencontre amoureuse entre deux personnes…

Cet évènement a déclenché la décision rapide d’un nouveau projet  de vie en couple, avec pour toile de fonds la mise en stand-by de nos emplois en France et la volonté d’un éloignement géographique, laissant une place certaine à plusieurs inconnues…

Notre passion commune pour le Maroc a permis de valider la destination où notre vie privée et professionnelle allait dorénavant se poursuivre, abandonnant nos carrières de cadre bancaire et de chargée de mission pour la Chambre de Commerce et d’Industrie de La Rochelle, sans compter les années passées dans le domaine de la décoration.

Nous avons quitté le confort douillet de l’agglomération rochelaise pour changer de vie, un 5 octobre 2004..

Nous connaissions le Maroc et Essaouira. La belle Mogador nous a très vite séduit par son côté authentique mais aussi par l’accueil de ses habitants, sa notion de proximité, sa médina cœur historique de la ville sans voitures, son port, son climat (que de ressemblances avec notre ville de départ, La Rochelle !). Les arguments n’ont pas manqué pour nous faire comprendre que nous étions au bon endroit. une fois installés.

Là encore, le hasard a joué les bonnes fées et généré rapidement les nombreuses activités professionnelles que nous exerçons encore aujourd’hui avec tant de passion.

Une nouvelle rencontre avec un entrepreneur marocain nous a permis de commencer la construction d’un immeuble de 7 appartements dans la nouvelle-ville d’Essaouira, quartier en développement.

Stéphane a donc embrassé la carrière d’entrepreneur en bâtiment et surfé sur l’engouement des européens pour le Maroc.

De nombreuses réalisations ont à ce jour complété notre carte de visite (rénovations diverses en médina et constructions de maisons en campagne, développement d'un parc structuré de gestion locative, etc...).

Dès notre installation à Essaouira en 2004, nous avions « tracé la route »…

Aujourd’hui, notre force réside dans notre complémentarité. Stéphane s’occupe des ventes de produits immobiliers, de toutes les formalités administratives, des suivis de chantiers, etc…

De mon côté, j’adapte au mieux les biens que nous vendons à Essaouira et sa région (riads, villas sur le golf, maisons de campagne, appartements, etc), en les aménageant et les décorant dans le but d’optimiser leur rentabilité locative.

Notre souci premier reste de satisfaire notre clientèle et de l’accompagner dans ses projets d’installation et de rentabilité locative à Essaouira, dans les meilleures conditions.

Notre entreprise reste la structure idéale sur laquelle les candidats étrangers à l’accession à la propriété à Essaouira peuvent s’appuyer.

Pour la plus grande tranquillité d’esprit de nos clients, nous mettons tout en œuvre pour assurer personnellement le suivi de chaque affaire y compris les formalités administratives, sujet lourd mais néanmoins incontournable.

Actuellement, notre parfaite connaissance du marché immobilier local en terme de transactions et de locations, couplé à nos savoirs faire en matière d’aménagement et de décoration, nous permet de pérenniser notre notoriété et nos capacités à continuer d’entreprendre au Maroc.

Nous restons à votre écoute au sein de notre agence.

A bientôt

Natacha LAURENT-SCHOPPE & Stéphane LAURENT")));
        $page->setArticle($articleAgence);
        $page->setCategory($tagAgence);
        $page->addTag($tagAgence);
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $manager->persist($page);
        $manager->flush();

        /**************************************************************
        * WEBPAGES :  NOS SERVICES
        ***************************************************************/
        $page = new WebPage();
        $page->setMainEntityOfPage('Article');
        $page->setCategory($tagNosServices);
        $page->addTag($tagNosServices);
        $page->setHeadline('Nos services');
        $page->setArticle($articleAgence);
        $page->setGallery($galleryHorizontal);
        $component = $manager->getRepository(Component::class)->findOneById(3);
        $page->addComponent($component);
        $page->setPushForward('Notre structure fonctionne en pôle immobilier couvrant tous vos besoins');
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline('Nos services');
        $page->setBlockQuoteTitle($faker->title);
        $page->setBlockQuote($faker->text);
        $video = $manager->getRepository(MediaObject::class)->findOneById(26);
        $page->addVideo($video);
        $page->setText(stripslashes(html_entity_decode('<p>R&eacute;novation, transformation, r&eacute;habilitation, agencement, agrandissement, am&eacute;nagement d&#39;appartements ou de maisons individuelles, de magasins, de bureaux, et de tous b&acirc;timents priv&eacute;s, professionnels et commerciaux. Etudes, plans, devis puis r&eacute;alisation et suivi du chantier.</p><p>L&rsquo;immobili&egrave;re d&rsquo;Essaouira est votre interlocuteur direct et privil&eacute;gi&eacute; pendant la dur&eacute;e de vos travaux. Notre mission de conseil en amont de votre projet vous apportera une aide efficace avant toute d&eacute;cision. Quelque soit l&#39;importance de vos besoins, nous pouvons prendre en charge tout ou partie de vos travaux de r&eacute;novation, de transformation, d&#39;am&eacute;nagement, de construction, d&#39;agrandissement, de d&eacute;coration ou d&#39;entretien de b&acirc;timent.</p><p>Positionn&eacute;e sur des prestations de qualit&eacute;, respectueuse des techniques et des m&eacute;thodes les mieux adapt&eacute;es &agrave; chaque chantier en prenant en compte les contraintes de nos clients, nous vous proposons un travail soign&eacute; et des tarifs adapt&eacute;s &agrave; chaque situation.</p>')));
        $manager->persist($page);
        $manager->flush();


        /**************************************************************
        * WEBPAGES :  LOCATION/VENTE
        ***************************************************************/
        $page = new WebPage();
        $page->setMainEntityOfPage('Accommodation');
        $page->setCategory($tagAgence);
        $page->addTag($tagAgence);
        $page->setHeadline('Location Immobilier');
        // $page->setSlug('la-location');
        $page->setPushForward('Location Essaouira: tous les biens disponibles présentés par notre structure, leader depuis 2004 !');
        $page->setPrimaryImage($image3);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline('Location');
        $page->setText(stripslashes('Location Essaouira: retrouvez tous les biens immobiliers à louer de la ville d\'Essaouira et de sa région selectionnés par l\'agence conseil l\’Immobilière d\'Essaouira. Découvrez la liste des meilleurs biens en location à Essaouira:  Riads, Appartements, Maisons et Villas de prestige au meilleur rapport qualité-prix et sur toutes durées (d\'une nuitée à une année et plus...)'));
        $manager->persist($page);
        $manager->flush();


        /**************************************************************
        * WEBPAGES :  LOCATION/VENTE
        ***************************************************************/
        $page = new WebPage();
        $page->setMainEntityOfPage('Accommodation');
        $page->setCategory($tagAgence);
        $page->addTag($tagAgence);
        $page->setHeadline('Achat Vente Immobilier');
        $page->setPushForward('Annonces immobilières');
        $page->setPrimaryImage($image2);
        $page->setSecondaryImage($image6);
        $page->setAlternativeHeadline('Achat immobilier Essaouira');
        $page->setText(stripslashes('Achat immobilier Essaouira et vente immobilier Essaouira: Pour tout investissement immobilier à Essaouira, l\'Immobilière d\'Essaouira, agence immobilière de référence à Essaouira depuis 2004 vous propose sa meilleure sélection de biens à vendre. Découvrez toutes les annonces immobilières Essaouira de vente immobilier Essaouira et d\'achat immobilier Essaouira.'));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagVente);
        $page->addTag($tagAgence);
        $page->setHeadline('Vente Local commercial');
        // $page->setSlug('vente-affaires-commerciales');
        $page->setPushForward("Notre sélection d\'affaires commerciales à Essaouira");
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("Fonds de commerce");
        $page->setText(stripslashes("Vente Fonds de commerce Essaouira: l'Agence l'Immobilière d'Essaouira vous propose une sélection de fonds de commerce à vendre à Essaouira et sa région.

De nombreux établissements de qualités vous seront proposés en fonction de critères précis comme l'emplacement ou encore le budget recherché, parce que la vente fonds de commerce Essaouira est une affaire de spécialiste contactez notre agence.

Nos spécialistes en investissement et en cession de fonds de commerce vous guideront pour trouver l'affaire qu'il vous faut. Contactez l'Immobilière d'Essaouira pour tous renseignements et toutes demandes concernant la vente fonds de commerce Essaouira. Retrouvez aussi la liste des Riads et Maisons d'Hôtes à vendre à Essaouira."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagLocation);
        $page->addTag($tagAgence);
        $page->setHeadline('Location Local commercial');
        // $page->setSlug('vente-affaires-commerciales');
        $page->setPushForward("Notre sélection d\'affaires commerciales à Essaouira");
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("Fonds de commerce");
        $page->setText(stripslashes("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagVente);
        $page->addTag($tagAgence);
        $page->setHeadline('Vente Riad');
        // $page->setSlug('vente-riad');
        $page->setPushForward("Notre sélection d\'achat et de vente Rias à Essaouira");
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("Riad ou dars");
        $page->setText(stripslashes(" Achat et Vente riad Essaouira: notre agence immobilère conseil l'Immobilière Essaouira vous propose une sélection de riad à vendre à Essaouira.

Découvrez les plus belles demeures de la médina d'Essaouira et profitez de l'expérience et de l'expertise d'une équipe de spécialistes de la Vente Riad Essaouira.

Qu'il s'agisse d'un riad à rénover ou d'un riad en excellent état, l'Immobilière d'Essaouira vous accompagnera dans toutes vos démarches de recherche, d'achat et vente riad Essaouira.

Contactez notre agence conseil pour tous renseignements concernant l'achat et la vente riad Essaouira. Retrouvez également notre sélection de riads en location."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagLocation);
        $page->addTag($tagAgence);
        $page->setHeadline('Location Riad');
        // $page->setSlug('vente-affaires-commerciales');
        $page->setPushForward("Notre sélection de riad à Essaouira");
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("Riad");
        $page->setText(stripslashes("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."));
        $manager->persist($page);
        $manager->flush();
        
        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagVente);
        $page->addTag($tagAgence);
        $page->setHeadline('Vente Appartement');
        // $page->setSlug('vente-appartement');
        $page->setPushForward('Notre sélection vente et achat appartement à Essaouira');
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("Appartements");
        $page->setText(stripslashes(" Vente & Achat Appartement Essaouira: notre agence immobilière conseil a selectionné pour vous les meilleurs offres de vente et d'achat appartement Essaouira.

En vous ouvrant les portes des plus beaux appartements de la ville et de la région, l'Immobilière d'Essaouira  vous permet de faire votre choix parmi les meilleurs affaires du marché.

Appartement neuf, promotion immobilière, appartement de standing et de caractère, notre sélection de vente et d'achat appartement Essaouira sera à la hauteur de vos attentes.

Consultez notre catalogue et contactez nos agents pour une étude personalisée de vos besoins. Visitez également nos pages location appartement mais aussi notre Livre d'Or."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagLocation);
        $page->addTag($tagAgence);
        $page->setHeadline('Location Appartement');
        // $page->setSlug('location-appartement');
        $page->setPushForward('Appartements à louer à Essaouira');
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("Appartements");
        $page->setText(stripslashes(" Location appartement Essaouira: Notre agence conseil dispose d'une sélection de location appartement Essaouira pour toutes vos demandes et besoins de location appartement Essaouira.

Notre agence dispose également d'un département gestion location pour optimiser la rentabilité de votre location appartement Essaouira. Nos conseillers pourront vous guider pour faire le choix d'un investissement de qualité.

Contactez nos équipes spécialisées et découvrez également nos services de décoration de villas et d'appartement Essaouira."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagVente);
        $page->addTag($tagAgence);
        $page->setHeadline('Vente Maison de campagne');
        // $page->setSlug('vente-maison-de-campagne');
        $page->setPushForward('Maison de campagne en vente à Essaouira');
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("Maison de campagne ");
        $page->setText(stripslashes("Vente maison Essaouira: L'immobilière d'Essaouira vous propose une sélection de maisons à vendre à Essaouira et dans sa région. Fort de son expérience et de son expertise, notre agence vous permettra d'aller directement à l'essentiel et vous guidera dans l'ensemble de vos démarches de recherche mais aussi d'achat de votre maison Essaouira.

Parce qu'un achat immobilier est avant tout un coup de coeur, L'Immobilière d'Essaouira vous accompagnera pour trouver le bien qui correspond à vos attentes.

Retrouvez également notre sélection de villas à vendre à Essaouira et sa région.

Contactez nous et consultez également notre agence pour toute demande spécifique et inscrivez vous à notre newsletter pour recevoir toutes les dernières nouveautés."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagLocation);
        $page->addTag($tagAgence);
        $page->setHeadline('Location Maison de campagne');
        // $page->setSlug('vente-affaires-commerciales');
        $page->setPushForward("Notre sélection Maison de campagne à Essaouira");
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("Fonds de commerce");
        $page->setText(stripslashes("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagVente);
        $page->addTag($tagAgence);
        $page->setHeadline('Vente Villa golf');
        // $page->setSlug('vente-affaires-commerciales');
        $page->setPushForward("Notre sélection Villa golf à Essaouira");
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("Villa golf");
        $page->setText(stripslashes("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagVente);
        $page->addTag($tagAgence);
        $page->setHeadline("Vente Maisons d'hotes");
        // $page->setSlug('vente-affaires-commerciales');
        $page->setPushForward("Notre sélection Maisons d'hotes à Essaouira");
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("Maisons d'hotes");
        $page->setText(stripslashes("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagLocation);
        $page->addTag($tagAgence);
        $page->setHeadline("Location Maisons d'hotes");
        // $page->setSlug('vente-affaires-commerciales');
        $page->setPushForward("Notre sélection Maisons d'hotes à Essaouira");
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("Maisons d'hotes");
        $page->setText(stripslashes("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."));
        $manager->persist($page);
        $manager->flush();

        /**************************************************************
        * WEBPAGES :  LOCATION/VENTE
        ***************************************************************/
        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagLocation);
        $page->addTag($tagAgence);
        $page->setHeadline('Location Villa golf');
        // $page->setSlug('location-villa-golf');
        $page->setPushForward('Location de villa de luxe sur le golf de Mogador');
        $page->setPrimaryImage($image1);
        $page->setSecondaryImage($image5);
        $page->setAlternativeHeadline("Villa golf mogador");
        $page->setText(stripslashes("Les villas de luxe du Golf de Mogador sont à louer en exclusivité grâce au partenariat établi entre votre agence l'Immobilière d'Essaouira et le site du Golf d'Essaouira, la Mogador Golf Academy ainsi que le Sofitel Mogador."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagLocation);
        $page->addTag($tagAgence);
        $page->setHeadline("Location terrain");
        // $page->setSlug('vente-affaires-commerciales');
        $page->setPushForward("Notre sélection terrain à Essaouira");
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("terrain");
        $page->setText(stripslashes("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagVente);
        $page->addTag($tagAgence);
        $page->setHeadline("Vente terrain");
        // $page->setSlug('vente-affaires-commerciales');
        $page->setPushForward("Notre sélection terrain à Essaouira");
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("terrain");
        $page->setText(stripslashes("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagVente);
        $page->addTag($tagAgence);
        $page->setHeadline("Vente location gérance");
        // $page->setSlug('vente-affaires-commerciales');
        $page->setPushForward("Notre sélection location gérance à Essaouira");
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("location gérance");
        $page->setText(stripslashes("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagLocation);
        $page->addTag($tagAgence);
        $page->setHeadline("Location location gérance");
        // $page->setSlug('vente-affaires-commerciales');
        $page->setPushForward("Notre sélection location gérance à Essaouira");
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("location gérance");
        $page->setText(stripslashes("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagLocation);
        $page->addTag($tagAgence);
        $page->setHeadline("Location affaires commerciales");
        // $page->setSlug('vente-affaires-commerciales');
        $page->setPushForward("Notre sélection affaires commerciales à Essaouira");
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("affaires commerciales");
        $page->setText(stripslashes("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."));
        $manager->persist($page);
        $manager->flush();

        $page = new WebPage();
        $page->setMainEntityOfPage('WebPage');
        $page->setCategory($tagVente);
        $page->addTag($tagAgence);
        $page->setHeadline("Vente affaires commerciales");
        // $page->setSlug('vente-affaires-commerciales');
        $page->setPushForward("Notre sélection affaires commerciales à Essaouira");
        $page->setPrimaryImage($image4);
        $page->setSecondaryImage($image7);
        $page->setAlternativeHeadline("affaires commerciales");
        $page->setText(stripslashes("Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."));
        $manager->persist($page);
        $manager->flush();
    }

    public function loadEvents($manager)
    {

         for ($i = 1; $i <= 2; $i++) {

            $faker = Faker\Factory::create('fr_FR');

            $event = new Event();

            $beginAt = new \DateTime('2009-09-30 20:24:00');
            
            $more = '+' . $i . ' day';
            
            $beginAt->modify($more);
            $endAt = $beginAt->modify($more);
            $event->setBeginAt($beginAt);
            $event->setEndAt($endAt);

            $person = $manager->getRepository(Person::class)->findOneById(2);
            $event->setPerson($person);

            $accommodation = $manager->getRepository(Accommodation::class)->findOneById(3);
            $event->setAccommodation($accommodation);
            
            $event->setComment($faker->text);
            $rentalType = $manager->getRepository(RentalType::class)->findOneById(2);
            $event->setRentalType($rentalType);
            $event->setDepositAmount($faker->randomDigitNotNull);
            $event->setDepositStatus($faker->boolean);
            $manager->persist($event);
        
        }
        $manager->flush();
        
    }

}
