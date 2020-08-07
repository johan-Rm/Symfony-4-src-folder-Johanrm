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
use Cocur\Slugify\Slugify;


class DeleteUnusedImagesCommand extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:delete-unused-images';

	private $container;

    private $em;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getEntityManager();

        parent::__construct();
    }

    protected function configure()
    {
        $this
        // the short description shown while running "php bin/console list"
        ->setDescription('Delete unused images.')
        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to delete unused images...')
        // ->addOption('no-write', null, InputOption::VALUE_OPTIONAL, 'No write ?', false)
        // ->addOption('write-only', null, InputOption::VALUE_OPTIONAL, 'Write only ?', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $assetsPath = $this->container->getParameter('assets.path');

        if ($this->lock()) {
            $start = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);

            $filesystem = new Filesystem();
            $slugify = new Slugify();
            
            $nonActiveMediaResults = $this->em->getRepository(MediaObject::class)
            ->findBy(array('isActive' => false));
            $count = count($nonActiveMediaResults);
            $output->writeln('counting of nonActiveMedia => ' . $count);
            $nonActiveMedia = [];
            foreach ($nonActiveMediaResults as $key => $entity) {
                $slug = $slugify->slugify($entity->getFilename());
                
                $nonActiveMedia[$slug] = $entity;
            }

            
            $activeMediaResults = $this->em->getRepository(MediaObject::class)
            ->findBy(array('isActive' => true));
            $count = count($activeMediaResults);
            $output->writeln('counting of activeMedia => ' . $count);

            $activeMedia = [];
            foreach ($activeMediaResults as $key => $entity) {
                $slug = $slugify->slugify($entity->getFilename());
                $activeMedia[$slug] = $entity;
            }

            $i = 0;
            $finder = new Finder();
            $path = $assetsPath . '/dist/';
            $finder->files()->in($path);
            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    
                    $absoluteFilePath = $file->getRealPath();
                    $fileNameWithExtension = $file->getRelativePathname();
                    $ext = pathinfo($fileNameWithExtension, PATHINFO_EXTENSION);
                    $file = basename($fileNameWithExtension);
                    $filename = basename($fileNameWithExtension, ".".$ext);
                    
                    $slug = $slugify->slugify($file);
                    if(isset($nonActiveMedia[$slug])) {
                        
                        if(!isset($activeMedia[$slug])) {

                            // dump($fileNameWithExtension);
                            // dump($file);
                            // dump($entity->getFilename());
                            // dump($absoluteFilePath);
                            $filesystem->remove($absoluteFilePath);    
                            $i++;
                        }
                    }

                }
            }
            // die();
        
            $output->writeln('counting deleted media => ' . $i);
            $end = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);
            $output->writeln('end to => ' . $end);
        }
    }


}