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
    /**
     * Return the events this subscriber listens for.
     *
     * @return array The subscribed events.
     */
    public static function getSubscribedEvents()
    {
        return [
            FieldCollectionEvent::NAME => [
                ['addScheduleFields', 0],
                ['addEventFields', 0],
            ],
        ];
    }

    /**
     * Add schedule fields to search.
     *
     * @param FieldCollectionEvent $event The field collection event.
     *
     * @return void
     */
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

    /**
     * Add event fields to search.
     *
     * @param FieldCollectionEvent $event The field collection event.
     *
     * @return void
     */
    public function addEventFields(FieldCollectionEvent $event): void
    {
        $event->addFields([
            'name',
            'description',
            'author',
        ]);
    }
}
