<?php

namespace App\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;

abstract class AbstractFixtures extends Fixture implements ContainerAwareInterface
{
  protected $empty = false;

  protected $container;

  public function setContainer(ContainerInterface $container = null)
  {
      $this->container = $container;
  }
}
