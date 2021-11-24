<?php
// src/EventSubscriber/CalendarSubscriber.php
namespace App\EventSubscriber;

use CalendarBundle\Entity\Event;
use App\Repository\TaskRepository;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Event\CalendarEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CalendarSubscriber implements EventSubscriberInterface
{
    private $taskRepository;
    private $router;

    public function __construct(
        TaskRepository $taskRepository,
        UrlGeneratorInterface $router
    ) {
        $this->taskRepository = $taskRepository;
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
        $tasks = $this->taskRepository->findAll();
        dd($tasks);

        foreach ($tasks as $task) {
            // this create the events with your data to fill calendar
            $taskEvent = new Event(
                $task->getName(),
                $task->getCreatedAt(),
                $task->getDueAt(),
                $task->getDescription()
            );
            dd($taskEvent);

            /*
             * Add custom options to events
             *
             * For more information see: https://fullcalendar.io/docs/event-object
             * and: https://github.com/fullcalendar/fullcalendar/blob/master/src/core/options.ts
             */

            // $taskEvent->setOptions([
            //     'backgroundColor' => 'red',
            //     'borderColor' => 'red',
            // ]);

            // $taskEvent->addOption(
            //     'url',
            //     $this->router->generate('task_show', [
            //         'id' => $task->getId(),
            //     ])
            // );

            // finally, add the event to the CalendarEvent to fill the calendar
            $calendar->addEvent($taskEvent);
        }
    }
}
