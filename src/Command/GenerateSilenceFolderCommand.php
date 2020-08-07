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



class GenerateSilenceFolderCommand extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:generate-silence-folder';

	private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        
        parent::__construct();
    }

    protected function configure()
    {
        $this
        // the short description shown while running "php bin/console list"
        ->setDescription('Generate silence folder for security.')
        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to generate silence folder for security....')
        ; 
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
         if ($this->lock()) {

            $start = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);

            $frontendPath = $this->container->getParameter('assets.path');
            $silenceFolder = $this->container->get('app.nuxtjs.silence_folder');
            
            $silenceFolder->generate($frontendPath . DIRECTORY_SEPARATOR . 'uploads');
            $silenceFolder->generate($frontendPath . DIRECTORY_SEPARATOR . 'media');
            $silenceFolder->generate($frontendPath . DIRECTORY_SEPARATOR . 'pdf');
            $silenceFolder->generate($frontendPath . DIRECTORY_SEPARATOR . 'js');
            $silenceFolder->generate($frontendPath . DIRECTORY_SEPARATOR . 'images');

            $end = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);
            $output->writeln('end to => ' . $end);
        }
    }


}