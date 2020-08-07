<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Google\Cloud\Translate\TranslateClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use App\Entity\Gender;
use App\Entity\PaymentMethod;
use App\Entity\AccommodationAmenity;
use App\Entity\AccommodationType;
use App\Entity\AccommodationPlace;
use App\Entity\AccommodationNature;
use App\Entity\AccommodationLocation;
use App\Entity\DocumentObjectType;
use App\Entity\AccommodationLabel;
use App\Entity\RentalType;
use App\Entity\RentalPriceType;
use App\Entity\Accommodation;
use App\Entity\WebPageTemplate;
use App\Entity\Tag;
use App\Entity\OrganizationType;
use App\Entity\MediaObject;
use App\Entity\Address;
use App\Entity\RealEstateAgent;
use App\Entity\Person;
use App\Entity\PersonNature;
use App\Entity\PersonPosition;
use App\Entity\Translation;
use App\Entity\Organization;


class AppFixtures extends AbstractFixtures
{
    private $genders = array();
    private $paymentMethods = array();
    private $accommodationAmenities = array();
    private $accommodationLocation = array();
    private $accommodationTypes = array();
    private $accommodationNatures = array();
    private $accommodationPlaces = array();
    private $accommodationLabels = array();
    private $documentObjectTypes = array();
    private $rentalTypes = array();
    private $rentalPriceTypes = array();
    private $webpageTemplates = array();
    private $tags = array();
    private $organizationTypes = array();
    private $values = array();

    public function __construct()
    {
        $this->setMetaDataTabs();
    }

    public function load(ObjectManager $manager)
    {
        $this->loadAccommodationMetaData($manager);
        $this->loadDefaultTranslations($manager);
    }

    public function loadAccommodationMetaData(ObjectManager $manager)
    {
        foreach($this->tags as $tag) {
            $entity = new Tag();
            $entity->setName($tag);
            $manager->persist($entity);
        }
        $manager->flush();

        foreach($this->accommodationLabels as $accommodationLabel) {
            $entity = new AccommodationLabel();
            $entity->setName($accommodationLabel);
            $manager->persist($entity);
        }
        $manager->flush();

        foreach($this->accommodationLocation as $accommodationLocation) {
            $entity = new AccommodationLocation();
            $entity->setName($accommodationLocation);
            $manager->persist($entity);
        }
        $manager->flush();

        foreach($this->webpageTemplates as $webpageTemplate) {
            $entity = new WebPageTemplate();
            $entity->setName($webpageTemplate);
            $manager->persist($entity);
        }
        $manager->flush();

        foreach($this->genders as $gender) {
            $entity = new Gender();
            $entity->setName($gender);
            $manager->persist($entity);
        }
        $manager->flush();

        foreach($this->paymentMethods as $paymentMethod) {
            $entity = new PaymentMethod();
            $entity->setName($paymentMethod);
            $manager->persist($entity);
        }
        $manager->flush();


        foreach($this->accommodationAmenities as $accommodationAmenity) {
            $entity = new AccommodationAmenity();
            if(is_array($accommodationAmenity)) {
                $entity->setName($accommodationAmenity[0]);
                $entity->setWithPicto($accommodationAmenity[1]);
                $entity->setSlugPicto($accommodationAmenity[2]);
            } else {
                $entity->setName($accommodationAmenity);
                $entity->setWithPicto(false);
            }

            $manager->persist($entity);
        }
        $manager->flush();

        foreach($this->accommodationPlaces as $accommodationPlace) {
            $entity = new AccommodationPlace();
            $entity->setName($accommodationPlace);
            $entity->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');
            $manager->persist($entity);
        }
        $manager->flush();

        foreach($this->accommodationTypes as $accommodationType) {
            $entity = new AccommodationType();
            $entity->setName($accommodationType);
            $manager->persist($entity);
        }
        $manager->flush();

        foreach($this->organizationTypes as $organizationType) {
            $entity = new OrganizationType();
            $entity->setName($organizationType);
            $manager->persist($entity);
        }
        $manager->flush();

        foreach($this->accommodationNatures as $accommodationNature) {
            $entity = new AccommodationNature();
            $entity->setName($accommodationNature);
            $manager->persist($entity);
        }
        $manager->flush();

        foreach($this->documentObjectTypes as $documentObjectType) {
            $entity = new DocumentObjectType();
            $entity->setName($documentObjectType);
            $manager->persist($entity);
        }
        $manager->flush();

        foreach($this->positions as $position) {
            $entity = new PersonPosition();
            $entity->setName($position);
            $manager->persist($entity);
        }
        $manager->flush();

        foreach($this->personNatures as $personNature) {
            $entity = new PersonNature();
            $entity->setName($personNature);
            $manager->persist($entity);
        }
        $manager->flush();

        foreach($this->rentalTypes as $rentalType) {
            $entity = new RentalType();
            $entity->setName($rentalType);
            $manager->persist($entity);
        }
        $manager->flush();

        foreach($this->rentalPriceTypes as $rentalPriceType) {
            $entity = new RentalPriceType();
            $entity->setName($rentalPriceType);
            $manager->persist($entity);
        }
        $manager->flush();

    }

    public function loadDefaultTranslations($manager)
    {
        // $translate = new TranslateClient(
        //     ['key' => 'AIzaSyCVeIkJL0QqHjiBNyQIyrFgAD758VeSkBs']
        // );
        $translationRepository = $manager->getRepository(Translation::class);
        $languages = array('fr', 'en');

        foreach ($languages as $language) {
            foreach($this->values as $key => $value) {
                $chain = html_entity_decode($value, ENT_QUOTES);
                
                // $translation = $translate->translate($chain, [
                //     'target' => $language
                // ]);
                // die('andek translation!');
                $translation = null;
                $translation = html_entity_decode(
                    $translation['text']
                    , ENT_QUOTES
                );

                $entity = new Translation();
                $entity->setLang($language);
                $entity->setEntityName('null');
                $entity->setEntityId('null');
                $entity->setHashKey('null');
                $entity->setFieldName('null');
                $entity->setKeyLeft($chain);
                $entity->setIsLocked(false);
                $entity->setValueRight($translation);
                $manager->persist($entity);
                $manager->flush();
            }
        }
    }

    private function setMetaDataTabs()
    {
        $this->values = array(
            'Derniers articles',
            'vente',
            'location',
            'nos experts',
            'decoration',
            'agence',
            'accueil',
            'calme',
            'jardin',
            'Experience Professionnelle',
            'nos services',
            'post',
            'Blog',
            'plus d\'infos',
            'Projets',
            'Accomplis',
            'Tags',
            'Liens utiles',
            'mois',
            'description',
            'Détails',
            'Identifiant de la propriété',
            'Prix',
            'Type de propriété',
            'Salle de bain',
            'Propriété Taille du lot',
            'Aire d\'atterrissage',
            'Endroit',
            'fonctionnalités',
            'plan d\'étage',
            'Chercher',
            'Partager cette publication',
            'vidéos',
            'appelez nous',
            'Taille',
            'Pièces',
            'Garage',
            'Numéro de téléphone',
            'Adresse électronique',
            'Lire la suite',
            'Soumettre',
            'adresse',
            'ville & pays',
            'Rénovation',
            'Construction',
            'Location saison',
            'Longue durée',
            'chambres',
            'Réf. agence',
            'Téléphone',
            'Vidéo',
            'Localisation',
            'détails',
            'plan d\'étage',
            'video',
            'Une erreur est survenue',
            'Votre message a été envoyé',
            'Votre message a bien été envoyé',
            'Veuillez saisir un prénom',
            'Veuillez saisir un nom',
            'Veuillez saisir un email',
            'Veuillez saisir un message',
            'Veuillez saisir un email valide',
            'Merci de patienter',
            'Nous vous répondrons dans les meilleurs délais',
            'Derniers biens',
            'Chambres',
            'Surface',
            'Terrain',
            'Terrasse',
            'jacuzzi',
            'garage',
            'cheminée',
            'Notre galerie',
            'salle de bain',
            'piscine',
            'wifi',
            'video',
            'veuillez saisir un email valide',
            'veuillez saisir un message',
            'veuillez saisir un email',
            'veuillez saisir un nom',
            'veuillez saisir un prénom',
            'Politique de confidentialité',
            'Contactez nous',
            'Termes et conditions',
            'N\'hésitez pas à lui envoyer un email',
            'Vous n\'avez pas réussi à joindre notre agent ?',
            'Envoyer',
            'Contacter l\'agence',
            'Message',
            'E-mail',
            'Prénom',
            'Nom',
            'Voir plus de détails',
            'A propos de nous',
            'localisation',
            'Saisir la référence',
            'Budget max.',
            'Budget max',
            'Budget min.',
            'Budget min',
            'de surface de terrain',
            'Biens similaires',
            'Rechercher un bien immobilier',
            'Vacances',
            'vous connaissez votre référence ?',
            'Achat'

        );

        $this->organizationTypes = array(
            'societe' => 'Société',
            'lien-reseau-social' => 'Lien réseau social'
        );

        $this->accommodationLabels = array(
            'exclusivite' => 'Exclusivité',
            'coup-de-coeur' => 'Coup de coeur'
        );

        $this->tags = array(
            'villa-piscine' => 'Villa Piscine',
            'jardin' => 'Jardin',
            'double-vitrage' => 'Double vitrage',
            'villa-luxe' => 'Villa luxe',
            'calme' => 'Calme',
            'appartement-standing' => 'Appartement standing'
        );

        $this->webpageTemplates = array(
            'default' => 'Default',
            'home' => 'Home',
            'about-us' => 'About Us',
            'contact' => 'Contact'
        );
        $this->positions = array(
              'notaire' => 'Notaire',
              'gardien' => 'Gardien'
          );

          $this->genders = array(
              'm' => 'M.',
              'f.' => 'Mme.'
          );

          $this->paymentMethods = array(
              'cheque' => 'Chèque',
              'virement' => 'Virement',
              'espece' => 'Espèce',
              'account-france' => 'Account France',
              'account-maroc' => 'Account Maroc'
          );


            $this->accommodationAmenities = array(
                'piscine' => array('Piscine', true, 'swimming-pool'),
                'piscine-chauffee' => array('Piscine chauffée', false, null),
                'climatisation' => array('Climatisation', false, null),
                'climatisation-reversible' => array('Climatisation reversible', false, null),
                'garage' => array('Garage', true, 'warehouse'),
                'jacuzzi' => array('Jacuzzi', true, 'hot-tub'),
                'wifi' => array('Wifi', true, 'wifi'),
                'air-conditionne' => 'Air conditionné',
                'balcon' => 'Balcon',
                'room-service' => 'Room service',
                'salle-de-sport' => 'Salle de sport',
                'parking' => 'Parking',
                'alarme' => 'Alarme',
                'gardien' => 'Gardien',
                'salle-de-bain' => array('Salle de bain', true, 'bath'),
                'cheminee' => array('Cheminée', true, 'fire'),
                'terrasse' => 'Terrasse',
                'solarium' => 'Solarium',
                'parking' => 'Parking',
                'ascenseur' => 'Ascenseur',
                'vue-sur-mer' => 'Vue sur mer',
                'hammam' => 'Hammam',
                'terrasse-attenante' => 'Terrasse attenante',
                'vue-degagee' => 'Vue dégagée',
                'exposition-sud' => 'Exposition Sud'
          );

          $this->accommodationTypes = array(
            'appartement' => 'appartement',
            'riad' => 'riad',
            'maisons-d-hotes' => 'maisons d\'hotes',
            'villa-golf' => 'villa golf',
            'maison-de-campagne' => 'maison de campagne',
            'maison-de-ville' => 'maison de ville',
            'terrain' => 'terrain',
            'affaires-commerciales' => 'affaires commerciales',
            'location-gerance' => 'location gérance',
            'local-commercial' => 'local commercial'
          );

        $this->accommodationLocation = array(
            'longue-duree' => 'Longue durée',
            'saisonniere' => 'Saisonnière'
        );


        $this->personNatures = array(
                'locataire' => 'Locataire',
                'acheteur' => 'Acheteur'
        );

        $this->accommodationNatures = array(
                'location' => 'Location',
                'vente' => 'Vente'
        );

          $this->accommodationPlaces = array(
            'medina' => 'medina',
            'nouvelle-ville' => 'nouvelle-ville',
            'golf-mogador' => 'golf mogador',
            'campagne' => 'campagne',
            'diabet' => 'diabet',
            'ghazoua' => 'ghazoua',
            'douar-larab' => 'douar larab',
            'sidi-kaouki' => 'sidi kaouki',
            'ida-ougourd' => 'ida ougourd',
            'hrarta' => 'hrarta',
            'bouzama' => 'bouzama',
            'aeroport' => 'aeroport',
            'had-draa' => 'had draa',
            'ounagha' => 'ounagha',
            'moulay-bouzarktoun' => 'moulay bouzarktoun',
            'laraich' => 'laraich',
            'chicht' => 'chicht',
            'route-de-marrakech' => 'route de marrakech',
            'route-de-safi' => 'route de safi'
          );

          $this->documentObjectTypes = array(
              'plan' => 'Plan',
              'mandat' => 'Mandat',
              'titre' => 'Titre',
              'contrat' => 'Contrat'
          );

          $this->rentalTypes = array(
              'la' => 'Location appartement',
              'lm' => 'Location maison',
              'lvg' => 'Location villa du golf'
          );

          $this->rentalPriceTypes = array(
              'hebdomadaire' => 'À la semaine',
              'mensuelle' => 'Au mois',
          );
    }
}
