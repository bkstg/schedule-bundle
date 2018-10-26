<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\Timeline\EventSubscriber;

use Bkstg\TimelineBundle\Event\TimelineLinkEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventLinkSubscriber implements EventSubscriberInterface
{
    private $url_generator;

    /**
     * Create a new event link subscriber.
     *
     * @param UrlGeneratorInterface $url_generator The url generator service.
     */
    public function __construct(UrlGeneratorInterface $url_generator)
    {
        $this->url_generator = $url_generator;
    }

    /**
     * Return the events this subscriber listens for.
     *
     * @return array The subscribed events.
     */
    public static function getSubscribedEvents()
    {
        return [
            TimelineLinkEvent::NAME => [
                ['setInvitedLink', 0],
                ['setScheduledLink', 0],
            ],
        ];
    }

    /**
     * Set the link for invited actions.
     *
     * @param TimelineLinkEvent $event The timeline action event.
     *
     * @return void
     */
    public function setInvitedLink(TimelineLinkEvent $event): void
    {
        $action = $event->getAction();

        if ('invited' != $action->getVerb()) {
            return;
        }

        $event = $action->getComponent('indirectComplement')->getData();
        $event->setLink($this->url_generator->generate('bkstg_event_read', [
            'id' => $event->getId(),
            'production_slug' => $event->getGroups()[0]->getSlug(),
        ]));
    }

    /**
     * Set the link for scheduled actions.
     *
     * @param TimelineLinkEvent $event The timeline action event.
     *
     * @return void
     */
    public function setScheduledLink(TimelineLinkEvent $event): void
    {
        $action = $event->getAction();

        if ('scheduled' != $action->getVerb()) {
            return;
        }

        $production = $action->getComponent('indirectComplement')->getData();
        $schedule = $action->getComponent('directComplement')->getData();
        $event->setLink($this->url_generator->generate('bkstg_schedule_read', [
            'id' => $schedule->getId(),
            'production_slug' => $production->getSlug(),
        ]));
    }
}
