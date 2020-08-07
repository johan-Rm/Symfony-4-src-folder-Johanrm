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
use App\Entity\Gallery;



class ReOrderImagesGallery extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:re-order-images-gallery';

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
        // ->addOption('no-write', null, InputOption::VALUE_OPTIONAL, 'No write ?', false)
        // ->addOption('write-only', null, InputOption::VALUE_OPTIONAL, 'Write only ?', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->lock()) {

            $start = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);

            $batchSize = 10;$j = 0;
            $results = $this->em->getRepository(Gallery::class)->findAll();
            foreach ($results as $key => $gallery) {
                $images = $gallery->getImageGalleries();
                // dump('gallery id : ' . $gallery->getId());
                

                
                $i = 0;
                foreach($images as $imageGallery) {
                    $i++;
                    // dump($imageGallery->getImage()->getFilename());
                    
                    $imageGallery->setPosition($i);
                    // dump('position : ' . $imageGallery->getPosition());
                    
                    $this->em->persist($imageGallery);
                    
               
                    
                }
// dump('-------------------');
                // if (($i % $batchSize) === 0) {
                    
                    // $this->em->clear(); // Detaches all objects from Doctrine!
                // }

                // $this->em->flush();
                // dump($gallery->getId());
                $j++;
                 if($j > 10) {
                    // break;
                }
    // break;
            }

            $this->em->flush();
           
            $end = date('Y-m-d\ H:i:s.u');
            $output->writeln('end to => ' . $end);
        }
    }


}