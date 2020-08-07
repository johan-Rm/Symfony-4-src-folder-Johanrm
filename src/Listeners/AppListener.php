<?php

namespace App\Listeners;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;



/**
 * AppListener
 */
class AppListener
{
    private $orm;
    private $container;

    public function __construct(ContainerInterface $container, EntityManagerInterface $orm)
    {
        $this->orm = $orm;
        $this->container = $container;
    }

    
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        
        
    }

}
