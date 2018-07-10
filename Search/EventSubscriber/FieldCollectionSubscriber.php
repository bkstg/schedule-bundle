<?php

namespace Bkstg\ScheduleBundle\Search\EventSubscriber;

use Bkstg\SearchBundle\Event\FieldCollectionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FieldCollectionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FieldCollectionEvent::NAME => [
                ['addScheduleFields', 0],
                ['addEventFields', 0],
            ]
        ];
    }

    public function addScheduleFields(FieldCollectionEvent $event)
    {
        $event->addFields([
            'name',
            'description',
            'author',
            'event.name',
            'event.description',
        ]);
    }

    public function addEventFields(FieldCollectionEvent $event)
    {
        $event->addFields([
            'name',
            'description',
            'author',
        ]);
    }
}
