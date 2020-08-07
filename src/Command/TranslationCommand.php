<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Util\Inflector;
use Symfony\Component\Console\Input\InputOption;
use App\Entity\Translation;
use Google\Cloud\Translate\TranslateClient;
use Symfony\Component\Console\Helper\Table;


class TranslationCommand extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:generate-translation';

	private $container;

    private $em;

    private $translator;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->em = $this->container->get('doctrine')->getEntityManager();

        $this->translator = $this->container->get('app.translator');

        parent::__construct();
    }

    protected function configure()
    {
        $this
        // the short description shown while running "php bin/console list"
        ->setDescription('Generate all translations.')
        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to generate all translations...')
        ->addOption('write-only', null, InputOption::VALUE_OPTIONAL, 'Write only ?', false)
        ->addOption('translate-only', null, InputOption::VALUE_OPTIONAL, 'Translate only ?', false)
        ->addOption('default-data', null, InputOption::VALUE_OPTIONAL, 'Default data ?', false)
        ; 
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->lock()) {
            $start = date('Y-m-d\ H:i:s.u');
            $output->writeln('<info>start to => ' . $start . '</info>');

            $writeOnly = (false !== $input->getOption('write-only'))? true: false;
            $translateOnly = (false !== $input->getOption('translate-only'))? true: false;
   
            $this->loadDefaultTranslations();
       
            $output->writeln('<comment>translate only : ' . ((false == $translateOnly)? 'false': 'true') . '</comment>');
            $output->writeln('<comment>write only : ' . ((false == $writeOnly)? 'false': 'true') . '</comment>');
            if(false === $writeOnly) {
                $output->writeln('<info>process translation chain</info>');
                $this->translate($input, $output);
            }
            if(false === $translateOnly) {
                $output->writeln('<info>write translation file</info>');
                $this->translator->write();
            }

            $end = date('Y-m-d\ H:i:s.u');
            $output->writeln('end to => ' . $end);
        }
    }

    private function translate(InputInterface $input, OutputInterface $output) 
    {
        $finder = new Finder();
        $output->writeln('<comment>find all files in easy_admin config directory</comment>');
        $finder->files()->in(
            $this->container
            ->get('kernel')
            ->getProjectDir() . '/config/packages/easy_admin/entities/'
        );
        // check if there are any search results
        if ($finder->hasResults()) {
            $i = 0;
            $totalNewTranslate = [];
            foreach ($finder as $file) {

                $output->writeln('');
                $output->writeln('filename : ' . $file->getRelativePathname());
                
                $attributes = Yaml::parseFile($file->getRealPath());
                $entityName = $this->getEntityName($file->getRelativePathname());
                if(isset($attributes['easy_admin']['entities'][$entityName])) {
                    $attributes['easy_admin']['entities'][$entityName]['name'] = $entityName;
                    $attributes = $attributes['easy_admin']['entities'][$entityName];

                     if(isset($attributes['translatable']) && $attributes['translatable']) {

                        $output->writeln('<comment>translatable</comment>');
                        
                        $repository = $this->em->getRepository($attributes['class']);
                        $results = $repository->createQueryBuilder('c')
                                                ->getQuery()
                                                ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
                        
                        $output->writeln($entityName .' recording results => ' . count($results));

                        list($attributes['form']['fields'], $formFieldsForTable) = $this->getFormFields($attributes['form']['fields']);

                        $table = new Table($output);
                        $table
                            ->setHeaders(['Field Name', 'Translatable'])
                            ->setRows($formFieldsForTable)
                        ;
                        $table->render();

                        // Exclusion media temporaire ??
                        if($attributes['name'] !== 'Media') {
                            $this->translator->setValuesEntity($attributes['name']);
                            foreach($results as $key => $result) {
                                $this->translator->process(
                                    $result
                                    , $attributes
                                    , $result['id']
                                );
                                 
                                // if ($i > 0 && $i % 20 == 0) { sleep(3); } $i++;
                            }
                        }
                    }
                }
                $countString = count($this->translator->newTranslate);
                $countChars = $this->count_array_chars($this->translator->newTranslate);
                $output->writeln('<comment>new string to translate => ' . $countString . '</comment>');
                $output->writeln('<comment>number char to translate => ' . $countChars . '</comment>');
                $totalNewTranslate = array_merge($this->translator->newTranslate, $totalNewTranslate);
                $this->translator->newTranslate = [];
            }

            $totalCountString = count($totalNewTranslate);
            $totalCountChars = $this->count_array_chars($totalNewTranslate);
            $output->writeln('');
            $output->writeln('<info>total </info><comment>new string to translate => ' . $totalCountString . '</comment>');
            $output->writeln('<info>total </info><comment>number char to translate => ' . $totalCountChars . '</comment>');
        }
    }

    private function getFormFields($fields)
    {
        $formFields = []; 
        $formFieldsForTable = [];
        foreach($fields as $key => $value) {
            if(isset($value['property'])) {
                $formFields[$value['property']] = $value;    
                $formFieldsForTable[] = [$value['property'], (isset($value['translatable'])? 'true': 'false')];    
            }
        }

        return [$formFields, $formFieldsForTable];
    }

    private function loadDefaultTranslations()
    {
         $values = array(
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
            'Achat',
            "Types de bien",
            "voir les avis",
            "D'Experience Professionnelle",
            "Vous connaissez votre référence ?",
            "Budget maxi.",
            "Budget mini.",
            "votre bien",
            "Rechercher",
            "trouvez-nous sur les réseaux sociaux",
            "numéro de page",
            "suivante",
            "dernière",
            "lancer votre recherche",
            "partager cet article",
            "e-mail",
            "prénom",
            "nom",
            "message",
            "démarer la vidéo",
            "articles similaires",
            "Partager ce bien",
            "Référence",
            "notre avis",
            "Imprimer",
            "Spécialiste",
            "depuis 2004",
            "en immobilier",
            "locations saisonnières, longues durées, ventes, constructions, rénovations, home staging",
            "Longue durée",
            "ans",
            'première',
            'précédente',
            'démarrer la vidéo',
            'Partager cette page',
            "Exclusivités",
            "Témoignages",
            "Médias",
            "veuillez saisir un numéro de téléphone",
            "veuillez saisir un numéro de téléphone valide",
            "phone",
            "Lieu",
            "la description",
            "Aucun bien immobilier",
            "n'a été trouvé",
            "phone",
            "logements trouvés",
            "logement trouvé",
            "chambre",
            "au mois",
            "À la semaine",
            "Avis clients",
            "Ils parlent de nous",
            "NOUS SAVONS FAIRE PREUVE DE RÉACTIVITÉ ET DE REPORTING PAR L'UTILISATION DES MOYENS D'ÉCHANGES RAPIDES (TÉLÉPHONE, WHATSAPP, MESSENGER, ETC...).<br/>TOUTES LES INFORMATIONS IMPORTANTES SONT FORMALISÉES PAR ÉCRIT.<br/>des retours transparents sont transmis aux vendeurs de biens apres chaque visite pour OPTIMISATION et valorisation de la relation etablie.",
            "Nous sommes à l'écoute de nos clients et défendons la notion de service.<br/>Un accompagnement et un suivi professionnel entre la  signature d'un compromis et d'un acte final et une écoute particulière avec interventions rapides s'agissant de nos locataires.",
            "Un climat de confiance réciproque est la base d'une relation saine et durable.<br/>Nous mettons tout en oeuvre pour privilégier cette conviction.",
            "Nous travaillons sur la base de mandats simples ou exclusifs, et de contrats formalisés par écrit et signés par les parties, afin de garantir les intérêts de chacun.",
            "Nos Engagements",
            "Une équipe solidaire et engagée",
            "Notre structure fonctionne en pôle immobilier couvrant tous vos besoins",
            "Tout",
            "à partir"
        );
        
        $viewLangTranslationsPath = $this->container->getParameter('view.lang.translations.path');
        $languages = array('fr', 'en');
        foreach ($languages as $language) {
            foreach($values as $key => $value) {

                $chain = html_entity_decode($value, ENT_QUOTES);
                $filePath = $viewLangTranslationsPath . DIRECTORY_SEPARATOR . $language . '.json';
                $file = json_decode(file_get_contents($filePath), true);

                if(!array_key_exists($chain, $file)) {
                     // dump($file[$chain]);
                    if('fr' !== $language) {
                        $translation = $this->translator->translate(
                            $chain
                            , null
                            , $language
                        );

                    } else {
                        $translation = $chain;
                    }
                    

                    $entity = new Translation();
                    $entity->setLang($language);
                    $entity->setEntityName('null');
                    $entity->setEntityId('null');
                    $entity->setHashKey('null');
                    $entity->setFieldName('null');
                    $entity->setKeyLeft($chain);
                    $entity->setIsLocked(false);
                    $entity->setValueRight($translation);
                    // dump($entity);
                    // die('andek translation!');
                    $this->em->persist($entity);
                    $this->em->flush();
                }
            }
        }
    }

    private function getEntityName($relativePathname) 
    {
        $entity = \pathinfo($relativePathname, PATHINFO_FILENAME);
        $entity = ucfirst(Inflector::camelize($entity));

        return $entity;
    }

    private function count_array_chars(array $array)
    {
        $charNumber = 0;
        array_walk_recursive($array, function($val) use (&$charNumber)
        {
            $charNumber += strlen($val);
        });
        return $charNumber;
    }

}