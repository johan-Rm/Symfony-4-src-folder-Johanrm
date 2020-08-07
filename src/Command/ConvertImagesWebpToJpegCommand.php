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


class ConvertImagesWebpToJpegCommand extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:convert-all-images-webp-to-jpg';

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
        ->setDescription('Convert images to webp.')
        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to convert iamges to webp...')
        // ->addArgument('cache', InputArgument::OPTIONAL, ' ?')
        ->addOption('cache', null, InputOption::VALUE_OPTIONAL, ' ?', false)
        // ->addOption('write-only', null, InputOption::VALUE_OPTIONAL, 'Write only ?', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $assetsPath = $this->container->getParameter('assets.path');
        $path = $assetsPath . DIRECTORY_SEPARATOR . 'uploads/media/';
        $cache = $input->getOption('cache');
        if (false !== $cache) {
            $path = $assetsPath . DIRECTORY_SEPARATOR . 'media/cache/';
        }

        if ($this->lock()) {
            $start = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);

            $slugify = new Slugify();

            $results = $this->em->getRepository(MediaObject::class)->findAll();
            $medias = [];
            foreach ($results as $key => $entity) {
                $slug = $slugify->slugify($entity->getFilename());
                $medias[$slug] = $entity;
            }


            $filesystem = new Filesystem();
           
            $finder = new Finder();
            

            $finder->files()->in($path);
            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    
                    $absoluteFilePath = $file->getRealPath();
                    $filePath = $file->getPath();
                    $fileNameWithExtension = $file->getRelativePathname();
                    $ext = pathinfo($fileNameWithExtension, PATHINFO_EXTENSION);
                    $file = basename($fileNameWithExtension);
                    $filename = basename($fileNameWithExtension, ".".$ext);
                    $slug = $slugify->slugify($file);

                    // dump($entity);die;
                    if($ext === 'webp') {
                        // $filePath = $absoluteFilePath;
                        $sourceFilePath = $filePath . DIRECTORY_SEPARATOR . $filename . '.' . $ext;
                        $targetFilePath = $filePath . DIRECTORY_SEPARATOR . $filename . '.' . 'jpg';
                        $output->writeln('<info>' . $sourceFilePath . '</info>');

                        if(isset($medias[$slug])) {

                            // $cmd = [
                            //     '/usr/bin/dwebp',
                            //     $sourceFilePath,
                            //     '-o',
                            //     $targetFilePath
                            // ];

                            $cmd = [
                                '/usr/bin/convert',
                                $sourceFilePath,
                                $targetFilePath
                            ];

                            $targetFilename = pathinfo($targetFilePath,  PATHINFO_BASENAME);
                            
                            $process = new Process($cmd);
                            $process->setTimeout(900);
                            try {
                                $process->mustRun();
                                // dump($process->getOutput());
                                if (false === $cache) {
                                    
                                    $entity = $medias[$slug];
                                    $entity->setEncodingFormat('image/jpg');
                                    $entity->setContentSize(null);
                                    $entity->setFilename($targetFilename);
                                    $entity->setOriginalFilename($targetFilename);
                                    $entity->setName($targetFilename);
                                    $this->em->flush($entity);
                                }

                                $filesystem->remove($sourceFilePath);

                            } catch (ProcessFailedException $exception) {
                                throw new \RuntimeException($exception->getMessage());
                            }
                        }
                    }
                }
            }

            // die();
     
            $end = date('Y-m-d\ H:i:s.u');
            $output->writeln('end to => ' . $end);
        }
    }


}