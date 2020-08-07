<?php

namespace App\EventSubscriber;

use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Event as Rental;


class CalendarSubscriber implements EventSubscriberInterface
{
     /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendar)
    {
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $filters = $calendar->getFilters();

        // // You may want to make a custom query from your database to fill the calendar
        // $calendar->addEvent(new Event(
        //     'Event 1',
        //     new \DateTime('Tuesday this week'),
        //     new \DateTime('Wednesdays this week')
        // ));

        // // If the end date is null or not defined, it creates a all day event
        // $calendar->addEvent(new Event(
        //     'All day event',
        //     new \DateTime('Friday this week')
        // ));


        $bookings = $this->em->getRepository(Rental::class)->findAll();
            // ->createQueryBuilder('rental')
//            ->andWhere('b.beginAt BETWEEN :startDate and :endDate')
//            ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
//            ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'))
            // ->getQuery()->getResult();

       // dump($bookings);
       // die;

        foreach($bookings as $booking) {


            // this create the events with your own entity (here booking entity) to populate calendar
            $bookingEvent = new Event(
                $booking->getAccommodation()->getReference() . ' - ' . $booking->getPerson()->getFirstname() . ' ' . $booking->getPerson()->getLastname(),
                $booking->getBeginAt(),
                $booking->getEndAt() // If the end date is null or not defined, it creates a all day event
            );

            /*
             * Optional calendar event settings
             *
             * For more information see : Toiba\FullCalendarBundle\Entity\Event
             * and : https://fullcalendar.io/docs/event-object
             */
            // $bookingEvent->setUrl('http://www.google.com');
            // $bookingEvent->setBackgroundColor($booking->getColor());
            // $bookingEvent->setCustomField('borderColor', $booking->getColor());

//            dump($this->request);

            // $bookingEvent->setUrl(
            //     $this->router->generate('easyadmin', array(
            //         'action' => 'show',
            //         'entity' => 'Rental',
            //         'menuIndex' => 0,
            //         'subMenuIndex' => 1,
            //         'id' => $booking->getId(),
            //     ))
            // );

            // finally, add the booking to the CalendarEvent for displaying on the calendar
            $calendar->addEvent($bookingEvent);
        }
    }
}