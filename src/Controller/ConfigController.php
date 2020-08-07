<?php // src/Controller/DefaultController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations as Rest;
// use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\View;

/**
 * @Route("/config")
 */
class ConfigController extends FOSRestController
{
  /**
     *
     * @Get("/image_filters", name="config_filters")
     * @Rest\View()
     * @Method({"GET","OPTIONS"})
     *
     **/
     public function getSchemaImageFiltersAction(Request $request)
     {
        $filter_sets = $this->container->getParameter('liip_imagine.filter_sets');
       /**
        * Create JSON View
        */
        $view = View::create($filter_sets);
        $view->setFormat('json');

        return $view;
     }
}
