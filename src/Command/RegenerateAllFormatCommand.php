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
use App\Entity\MediaObject;
use App\Entity\RealEstateAgent;
use Liip\ImagineBundle\Service\FilterService;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;


class RegenerateAllFormatCommand extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:regenerate-all-image-formats';

	private $container;

    private $em;

    private $imagine;

    public function __construct(ContainerInterface $container, FilterService $imagine, CacheManager $cacheManager)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getEntityManager();
        $this->imagine = $imagine;
        $this->cacheManager = $cacheManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
        // the short description shown while running "php bin/console list"
        ->setDescription('Generate all image formats.')
        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to generate all image formats...')
        ->addOption('stamp', null, InputOption::VALUE_OPTIONAL, 'stamp ?', false)
        ->addArgument('dateBegin', InputArgument::OPTIONAL, 'dateBegin ?')
        ->addArgument('dateEnd', InputArgument::OPTIONAL, 'dateEnd ?')
        // ->addOption('write-only', null, InputOption::VALUE_OPTIONAL, 'Write only ?', false)
        ; 
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dateBegin = $input->getArgument('dateBegin');
        $dateEnd = $input->getArgument('dateEnd');
        $stamp = (false !== $input->getOption('stamp'))? true: false;
        $formatsToExclude = 'team_square,team_square_medium,team_square_small';
        $formatsToExclude = explode(",", $formatsToExclude);
        $formats = array('image/jpeg', 'image/png', 'image/webp', 'image/jp2', 'image/jxr');
        
        $frontendPath = $this->container->getParameter('assets.path');
        $folderMediaAssets = $this->container->getParameter('assets.media.folder');
        
        $pathMediaAssets = $frontendPath . DIRECTORY_SEPARATOR . $folderMediaAssets . DIRECTORY_SEPARATOR;
        $filter_sets = $this->container->getParameter('liip_imagine.filter_sets');


        /** @var CacheManager */
        // $imagineCacheManager = $this->container->get('liip_imagine.cache.manager');
        // $imagine = $this->container->get('liip_imagine.service.filter');

        $filesystem = new Filesystem();

        $realEstateAgentResults = $this->em->getRepository(RealEstateAgent::class)->findAll();
        $realEstateAgentMedias = [];
        foreach ($realEstateAgentResults as $key => $entity) {
            $primaryImageId = $entity->getPrimaryImage()->getId();
            $realEstateAgentMedias[$primaryImageId] = $entity;
        }

        if ($this->lock()) {

            $start = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);

            $dateBegin = new \Datetime($dateBegin);
            $dateEnd = new \Datetime($dateEnd);
            $mediaObjectRepository = $this->em->getRepository(MediaObject::class);
            $results = $mediaObjectRepository->findByDate(
                $dateBegin->format('Y-m-d')
                , $dateEnd->format('Y-m-d')
            );
            // dump(count($results));die;
            /**
            * Each all medias
            **/
            foreach ($results as $key => $media) {
                
               

               // cwebp -q 70 29ES_comp.jpg -o 29ES_comp.webp
                // dump($pathMediaAssets . $media->getFilename());die;

                if(null !== $media->getFilename()
                    && 
                    $filesystem->exists($pathMediaAssets . $media->getFilename())
                ) {
                    // $process = new Process(['ls', '-lsa']);
                    // try {
                    //     $process->mustRun();

                    //     echo $process->getOutput();
                    // } catch (ProcessFailedException $exception) {
                    //     echo $exception->getMessage();
                    // }
$output->writeln($pathMediaAssets . $media->getFilename());
                    if(in_array($media->getEncodingFormat(), $formats)) {

                        foreach($filter_sets as $key => $filter) {

                            /**
                            * Spécif à refactoriser un de ses 4
                            * ici nous les medias d'agents immobilier
                            * dont nous générons uniquement le format team_
                            **/
                            if(isset($realEstateAgentMedias[$media->getId()])) { 
                                if(in_array($key, $formatsToExclude)) {
                                    $this->imagine->getUrlOfFilteredImage(
                                        $folderMediaAssets . '/' . $media->getFilename()
                                        , $key
                                    );
                                    dump('key : ' . $key);
                                    dump('OK : ' . $media->getFilename());
                                }  
                            } else {
                                if(!in_array($key, $formatsToExclude)) {
                                    $this->cacheManager->remove(
                                        $folderMediaAssets . '/' . $media->getFilename()
                                        , $key
                                    );
                                    $this->imagine->getUrlOfFilteredImage(
                                        $folderMediaAssets . '/' . $media->getFilename()
                                        , $key
                                    );
                                    

                


                                    dump('key : ' . $key);
                                    dump('OK : ' . $media->getFilename());
                                    
                                }
                            }
                            
                            
                        }
                    }
                }
                


            }
            $end = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);
            $output->writeln('end to => ' . $end);
        }
    }


}