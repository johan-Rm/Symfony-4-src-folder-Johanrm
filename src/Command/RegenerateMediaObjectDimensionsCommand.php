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


class RegenerateMediaObjectDimensionsCommand extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:regenerate-media-object-dimensions';

	private $container;

    private $em;

    private $imagine;

    private $cacheManager;

    
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
        ->setDescription('Generate all media object dimensions')
        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to generate all media object dimensions...')
        // ->addOption('write-only', null, InputOption::VALUE_OPTIONAL, 'Write only ?', false)
        ; 
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $formats = array('image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/jp2', 'image/jxr');
        
        $frontendPath = $this->container->getParameter('assets.path');
        $folderMediaAssets = $this->container->getParameter('assets.media.folder');
        
        $pathMediaAssets = $frontendPath . DIRECTORY_SEPARATOR . $folderMediaAssets . DIRECTORY_SEPARATOR;

        if ($this->lock()) {

            $start = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);

            $mediaObjectRepository = $this->em->getRepository(MediaObject::class);
            $results = $mediaObjectRepository->findAll();
            $filesystem = new Filesystem();
         
            /**
            * Each all medias
            **/
            foreach ($results as $key => $media) {
                $sourceFilePath = $pathMediaAssets . DIRECTORY_SEPARATOR . $media->getOriginalname();
                if($filesystem->exists($sourceFilePath)) {
                      
                    $output->writeln('<info>' . $sourceFilePath . '</info>');
                    $dimensions = getimagesize($sourceFilePath);
                    $contentSize = filesize($sourceFilePath);

                    $media->setContentSize($contentSize);
                    $media->setDimensions($dimensions);
                    $this->em->flush($media);
                }
            }
            
            $end = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);
            $output->writeln('end to => ' . $end);
        }
    }
}