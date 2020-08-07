<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Command\LockableTrait;


class CheckCommand extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:check-command';


    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
        // the short description shown while running "php bin/console list"
        ->setDescription('Check command')
        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command for check')
        // ->addOption('no-write', null, InputOption::VALUE_OPTIONAL, 'No write ?', false)
        // ->addOption('write-only', null, InputOption::VALUE_OPTIONAL, 'Write only ?', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        if ($this->lock()) {
           

           
            dump('lockkkkk');
        } else {
            dump('uuuuuuunnnnnlockkkkk');
        }
        die();
    }


}