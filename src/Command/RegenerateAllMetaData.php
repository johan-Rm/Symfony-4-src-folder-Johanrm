<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Util\Inflector;
use Symfony\Component\Console\Input\InputOption;
use App\Entity\Article;
use App\Entity\WebPage;
use App\Entity\Accommodation;



class RegenerateAllMetaData extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:regenerate-all-meta-data';

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
        ->setDescription('Generate all meta data.')
        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to generate all meta data...')
        // ->addOption('no-write', null, InputOption::VALUE_OPTIONAL, 'No write ?', false)
        // ->addOption('write-only', null, InputOption::VALUE_OPTIONAL, 'Write only ?', false)
        ; 
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $seoMetaData = $this->container->get('app.meta_data');
        if ($this->lock()) {
            $start = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);

            $results = $this->em->getRepository(Article::class)->findAll();
            foreach ($results as $key => $entity) {
                $entity = $seoMetaData->process($entity);
                $this->em->flush($entity);
            }

            $results = $this->em->getRepository(WebPage::class)->findAll();
            foreach ($results as $key => $entity) {
                $entity = $seoMetaData->process($entity);
                $this->em->flush($entity);
            }
            
            $results = $this->em->getRepository(Accommodation::class)->findAll();
            foreach ($results as $key => $entity) {
                $entity = $seoMetaData->process($entity);
                $this->em->flush($entity);
            }

            $end = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);
            $output->writeln('end to => ' . $end);
        }
    }
}