<?php

namespace App\Service\NuxtJS;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Finder\Finder;
use Doctrine\Common\Util\Inflector;


class ExportJsonData
{
    private $container;
    private $em;

    public function __construct(ContainerInterface $container, EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    public function generate($entity, $options = [])
    {


        $host = $this->container->getParameter('cms.host');
        $client = HttpClient::create();
        $response = $client->request('GET', $host . '/api/articles');

        $statusCode = $response->getStatusCode();

        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $jsonContent = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
        $frontendPath = $this->container->getParameter('view.project_dir');
        dump($jsonContent);
        // $content = $response->toArray();

        dump($entity);
        die;

    }

}
