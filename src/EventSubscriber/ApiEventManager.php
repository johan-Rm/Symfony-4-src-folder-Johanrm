<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Exception\NotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


final class ApiEventManager implements EventSubscriberInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['checkAvailability', EventPriorities::PRE_VALIDATE],
        ];
    }

    public function checkAvailability(GetResponseForControllerResultEvent $event): void
    {
        // $params = $this->container->getParameter('nelmio_cors.defaults');
        // if(null !== $event->getRequest()->headers->get('origin')) {
        //     $origin = $event->getRequest()->headers->get('origin');
        //     if(!in_array($origin, $params['allow_origin'])) {

        //         throw new NotFoundException('UNAUTHORIZED REQUEST');

        //         // return new JsonResponse(
        //         //     'UNAUTHORIZED REQUEST',
        //         //     JsonResponse::HTTP_UNAUTHORIZED
        //         // ); 
        //     }
        // } else {
        //     throw new NotFoundException('UNAUTHORIZED REQUEST');
        // }
    }
}