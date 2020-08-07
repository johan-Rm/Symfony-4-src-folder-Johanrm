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
use Cocur\Slugify\Slugify;
use App\Entity\RealEstateAgent;


class GenerateJsonEntityFilesCommand extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:generate-json-entity-files';

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
        ->setDescription('Generate Json Entity Files.')
        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to generate json entity files...')
        // ->addOption('no-write', null, InputOption::VALUE_OPTIONAL, 'No write ?', false)
        // ->addOption('write-only', null, InputOption::VALUE_OPTIONAL, 'Write only ?', false)
        ;
    }

    /**
    *
    *   a suivre
    **/
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->lock()) {
            $start = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);

            // $filesystem = new Filesystem();
            // $slugify = new Slugify();

            $realEstateAgentResults = $this->em->getRepository(RealEstateAgent::class)->findAll();
            // $activeMedia = [];
            $exportJsonData = $this->container->get('app.nuxtjs.export_json_data');

            foreach ($realEstateAgentResults as $key => $entity) {
                // $slug = $slugify->slugify($entity->getFilename());
                // $activeMedia[$slug] = $entity;
                $exportJsonData->generate($entity);
            }

            
            // die();
            $end = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);
            $output->writeln('end to => ' . $end);
        }
    }


}
