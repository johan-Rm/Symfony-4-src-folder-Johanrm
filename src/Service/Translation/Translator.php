<?php

namespace App\Service\Translation;

use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\TranslatorInterface;
use Google\Cloud\Translate\TranslateClient;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Finder\Finder;
use App\Entity\Translation;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Util\Inflector;



class Translator
{
    public $newTranslate = [];

    private $isActive = true;

    /** @var ConfigManager */
    private $container;

    private $config;

    private $em;

    private $translator;

    private $identifier;

    private $languages;

    private $repository;

    private $slugify;

    private $translations = [];
    
    private $symfonyTranslator;
    

    public function __construct(ContainerInterface $container, EntityManager $em, TranslatorInterface $translator)
    {

        $this->symfonyTranslator = $translator;
        $this->slugify = new Slugify();
        $this->container = $container;
        $this->em = $em;
        $this->languages = array('fr', 'en');
        $this->translator = new TranslateClient(
            ['key' => 'AIzaSyCVeIkJL0QqHjiBNyQIyrFgAD758VeSkBs']
        );
        $this->repository = $this->em->getRepository(Translation::class);

        /**
        *
        * pour dedoublonner
        *
        * DELETE t0 FROM immobiliere_essaouira.translation t0
        * LEFT OUTER JOIN (
        *        SELECT MIN(id) as id, lang, field_name, entity_name, entity_id
        *        FROM immobiliere_essaouira.translation
        *        GROUP BY lang, field_name, entity_name, entity_id
        *    ) as t1 
        *    ON t0.id = t1.id
        * WHERE t1.id IS NULL
        * ;
        **/
    }

    public function setValuesEntity($entityName)
    {
        $results = $this->repository->findBy(
            array('entityName' => $entityName)
        );
        foreach ($results as $result) {
            
            $key = $this->createKey(
                $result->getLang()
                , $result->getEntityName()
                , $result->getFieldName()
                , $result->getEntityId()
            );
            $this->translations[$key] = $result;
        }
    }

    public function process($values, $config, $id = null)
    {
        $this->config = $config;
        $this->identifier = $id;
        
        $viewLangTranslationsSlugPath = $this->container->getParameter('view.lang.translations.slug.path');
        $viewLangTranslationsPath = $this->container->getParameter('view.lang.translations.path');

        // return true;
        $finder = new Finder();
        $finder->depth('== 0');
        // find all files in the current directory
        $finder->files()->in($viewLangTranslationsPath);
        // dump($this->container->getParameter('view.lang.translations.path'));
        // check if there are any search results
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $absoluteFilePath = $file->getRealPath();

                $fileNameWithExtension = $file->getRelativePathname();
                $language = \pathinfo($fileNameWithExtension, PATHINFO_FILENAME);

    
                // find all files in the current directory
                $slugFilePath = $viewLangTranslationsSlugPath . DIRECTORY_SEPARATOR . $fileNameWithExtension;
                $slugFile = json_decode(file_get_contents($slugFilePath), true);
                if(in_array($language, $this->languages)) {
                    $file = json_decode($file->getContents(), true);
                    $this->translateChainsOfTheEntity($values, $language, $file, $slugFile);
                }
            }
        }

        return true;
    }

    public function write()
    {
        if(!$this->isActive) {
            return false;
        }

        $finder = new Finder();
        $finder->depth('== 0');
        // find all files in the current directory
        $finder->files()->in($this->container->getParameter('view.lang.translations.path'));
        // check if there are any search results
        if($finder->hasResults()) {
            foreach($finder as $file) {
                // $absoluteFilePath = $file->getRealPath();
                $fileNameWithExtension = $file->getRelativePathname();
                $language = \pathinfo($fileNameWithExtension, PATHINFO_FILENAME);
                if(in_array($language, $this->languages)) {
                    $file = json_decode($file->getContents(), true);
                    $this->writeTranslationsInViewproject($language, $file);
                }
            }
        }
    }

    private function translateChainsOfTheEntity($values, $language, $file, $slugFile)
    {
        foreach($values as $fieldName => $value) {
            if(isset($this->config['form']['fields'][$fieldName])) {
                if(isset($this->config['form']['fields'][$fieldName]['translatable'])
                    && $this->config['form']['fields'][$fieldName]['translatable']
                ) {

                    /**
                    * Init variables and get entity if exist
                    **/
                    $chain = trim($value);
                    $entity = null;
                    $found = false;
                    $key = $this->createKey(
                        $language
                        , $this->config['name']
                        , $fieldName
                        , $this->identifier
                    );
                    
                    // à voir si on garde
                    if(isset($this->translations[$key])
                    ){ // si command symfony
                        $entity = $this->translations[$key];
                    } else if(empty($this->translations)
                    ) { // si mse a jour backoffice
                        $entity = $this->repository->findOneBy(
                            array(
                                'lang' => $language,
                                'entityName' => $this->config['name'],
                                'fieldName' => $fieldName,
                                'entityId' => $this->identifier
                            )
                        );  
                    }
  
                    /**
                    * translation takes place if :
                    *     not chain empty
                    *     not locked
                    *     not found html tag
                    *     not numeric string
                    *     
                    **/

                    $locked = (is_callable([$entity, 'getIsLocked']) && $entity->getIsLocked() === true)? true: false;
                    if(
                        !empty($chain) && false === $locked 
                        // && !$this->isHTML($chain)
                        && !is_numeric($chain)
                    ) {
                        $chain = html_entity_decode($chain, ENT_QUOTES);
                        if('fr' !== $language) { // all languages except French
                            $chainAddSlashes = $chain;
                            /**
                            * Before the translation we check if the chain exists
                            *     classic chain
                            *     slug chain
                            *     new chain
                            **/
                            if(
                                $this->isActive 
                                && (
                                    array_key_exists($chainAddSlashes, $file) 
                                    && !empty($file[$chainAddSlashes])
                                )
                                && 'slug' !== $fieldName
                            ) { // classic chain

                                $found = true;
                                $translation = $file[$chainAddSlashes];

                            } else if(
                                $this->isActive
                                && isset($slugFile[lcfirst($this->config['name'])])
                                && ( 
                                    array_key_exists($chainAddSlashes, $slugFile[lcfirst($this->config['name'])])
                                    && !empty($slugFile[lcfirst($this->config['name'])][$chainAddSlashes])
                                )
                                && 'slug' === $fieldName
                            ) { // slug chain

                                $found = true;
                                $translation = $slugFile[lcfirst($this->config['name'])][$chainAddSlashes];
                         
                            } else 
                            { // new chain

                                // dump('attention traduction non trouvé dans les json');
                                // die;
                            }

                            // dump('found : ' . $found);
                            // dump($found);
                            // dump($chain);

                            if($this->isActive && $found === false) {
                                
                                $translation = $this->translate(
                                    $chain
                                    , $fieldName
                                    , $language
                                );
                                $this->newTranslate[] = $chain;
                                // $translation = $chain;
                            }
                            
                        } else {
                            /**
                            * French language only
                            **/
                            $translation = $chain;  
                        }


                        $isHtml = false;
                        $keySlug = null;
                        if(isset($this->config['form']['fields'][$fieldName]['type']) 
                            && 'fos_ckeditor' == $this->config['form']['fields'][$fieldName]['type']  
                        ) {
                            $isHtml = true;
                            $keySlug = $this->generateKeySlug($language, $fieldName, $this->config['name'], $values['slug']);
                            
                        }
                        
                        /**
                        * Translation recording
                        **/
                        $entity = $this->translationRecording(
                            $chain
                            , $translation
                            , $fieldName
                            , $language
                            , $entity
                            , $isHtml
                            , $keySlug
                        );
                    }
                } 
            }
        }
        $this->em->clear();
    }

    public function translate($chain, $key, $language) 
    {
        if('slug' == $key) {

            $chain = str_replace("-", " ", $chain);
            $translation = $this->translator->translate(
                strtolower($chain)
                , [ 'target' => $language ]
            );
            $translation['text'] = html_entity_decode($translation['text'], ENT_QUOTES);
            $translation['text'] = $this->slugify->slugify($translation['text']);

            return $translation['text'];
                
        } else {
            
            $translation = $this->translator->translate(
                $chain
                , [ 'target' => $language ]
            );

            return html_entity_decode($translation['text'], ENT_QUOTES);    
        }
    }

    private function translationRecording($chain, $translation, $key, $language, $entity, $isHtml, $keySlug)
    {
        if(null === $entity) {
            $translatedFieldName = $this->symfonyTranslator->trans($key);
            $translatedEntityName = $this->symfonyTranslator->trans($this->config['name']);
            $entity = new Translation();
            $entity->setLang($language);
            $entity->setEntityName($this->config['name']);
            $entity->setTranslatedEntityName($translatedEntityName);
            $entity->setEntityId($this->identifier);
            $entity->setHashKey('null');
            $entity->setFieldName($key);
            $entity->setTranslatedFieldName($translatedFieldName);
            $entity->setKeyLeft($chain);
            $entity->setIsLocked(false);
            $entity->setIsHtml($isHtml);
            $entity->setValueRight($translation);
            $entity->setKeySlug($keySlug);
            $this->em->persist($entity);
        } else {
            $entity->setIsHtml($isHtml);
            $entity->setKeyLeft($chain);
            $entity->setValueRight($translation);
            $entity->setIsLocked(false);
            $entity->setKeySlug($keySlug);
            $this->em->merge($entity);
        }        
        $this->em->flush();

        return $entity;
    }

    private function writeTranslationsInViewproject($language, $file)
    {

        $translationRepository = $this->em->getRepository(Translation::class);
        $results = $translationRepository->findWithoutSlug($language);
       
        $translationJson = [];
        foreach($results as $result) {
            $key = $result->getKeyLeft();
            if(!empty($result->getKeySlug())) {
                $key = $result->getKeySlug();
            }
            $translationJson[$key] = $result->getValueRight();
        }
        
        $updatedFile = array_merge($file, $translationJson);
       
        $jsonLang = json_encode($updatedFile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);

        $filesystem = new Filesystem();
        try {
            $filesystem->mkdir($this->container->getParameter('view.lang.translations.path'));
            $filesystem->dumpFile($this->container->getParameter('view.lang.translations.path') . '/' . $language . '.json',  $jsonLang);
            $filesystem->dumpFile($this->container->getParameter('app.lang.translations.path') . '/accommodations.' . $language . '.json',  $jsonLang);
        } catch (IOExceptionInterface $exception) {
            echo "Translate : An error occurred while creating your directory at ".$exception->getPath();
            die;
        }

        /**
        * Nous traduisons pour les slugs uniquement les langues non françaises
        ***/
        $translationRepository = $this->em->getRepository(Translation::class);
        $results = $translationRepository->findWithSlug($language);
      
        $translationJson = [];
        /**
        * Nous inversons les clefs valeurs pour la traduction des slugs
        ***/
        foreach($results as $result) {
            $entityName = Inflector::camelize($result->getEntityName());
            $translationJson[$entityName][$result->getKeyLeft()] = $result->getValueRight();
        }

        if('en' == $language) {

            $translationJsonInversed = [];
            
            foreach($results as $result) {
                $entityName = Inflector::camelize($result->getEntityName());
                $translationJsonInversed[$entityName][$result->getValueRight()] = $result->getKeyLeft();
            }

            $updatedFile = $translationJsonInversed;

            $jsonLang = json_encode($updatedFile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);

            $filesystem = new Filesystem();
            try {
                $filesystem->mkdir($this->container->getParameter('view.lang.translations.slug.path'));
                $filesystem->dumpFile($this->container->getParameter('view.lang.translations.slug.path') . '/' . $language . '-fr.json',  $jsonLang);
                $filesystem->dumpFile($this->container->getParameter('app.lang.translations.path') . '/accommodations-slug.' . $language . '.json',  $jsonLang);
            } catch (IOExceptionInterface $exception) {
                echo "Translate : An error occurred while creating your directory at ".$exception->getPath();

                die;
            }

        }
        // dump($translationJson);die;

        /**
        * pourquoi fusionner si nous avons tous en base ?
        ***/
        // $updatedFile = array_merge($translationJson, $file);
        $updatedFile = $translationJson;

        $jsonLang = json_encode($updatedFile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);

        $filesystem = new Filesystem();
        try {
            $filesystem->mkdir($this->container->getParameter('view.lang.translations.slug.path'));
            $filesystem->dumpFile($this->container->getParameter('view.lang.translations.slug.path') . '/' . $language . '.json',  $jsonLang);
            $filesystem->dumpFile($this->container->getParameter('app.lang.translations.path') . '/accommodations-slug.' . $language . '.json',  $jsonLang);
        } catch (IOExceptionInterface $exception) {
            echo "Translate : An error occurred while creating your directory at ".$exception->getPath();

            die;
        }
        
    }

    private function createKey($language, $entityName, $fieldName, $entityId)
    {   
        $key = '';
        $key.= $language;
        $key.= '-' . $entityName;
        $key.= '-' . $fieldName;
        $key.= '-' . $entityId;
        $key = strtolower($key);

        return $key;
    }

    private function isHTML($string){
        return $string != strip_tags($string) ? true:false;
    }

    private function generateKeySlug($language, $fieldName, $entityName, $slug)
    {   
        $key = $language . ' ' . $fieldName . ' ' . $entityName;
        $key = $this->slugify->slugify($key, '.') . '.' . $slug;

        return $key;
    }
}
