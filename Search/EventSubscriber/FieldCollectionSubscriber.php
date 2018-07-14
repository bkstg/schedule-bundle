<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
            ],
        ];
    }

    public function addScheduleFields(FieldCollectionEvent $event): void
    {
        $event->addFields([
            'name',
            'description',
            'author',
            'event.name',
            'event.description',
        ]);
    }

    public function addEventFields(FieldCollectionEvent $event): void
    {
        $event->addFields([
            'name',
            'description',
            'author',
        ]);
    }
}
