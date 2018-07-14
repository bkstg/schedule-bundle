<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\Timeline\EventSubscriber;

use Bkstg\CoreBundle\Event\EntityPublishedEvent;
use Bkstg\ScheduleBundle\Entity\Event;
use Bkstg\ScheduleBundle\Entity\Schedule;
use Spy\Timeline\Driver\ActionManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class EventTimelineSubscriber implements EventSubscriberInterface
{
    private $action_manager;
    private $user_provider;
    private $url_genertor;

    /**
     * Create a new event notification listener.
     *
     * @param ActionManagerInterface $action_manager The action manager service.
     * @param UserProviderInterface  $user_provider  The user provider service.
     * @param UrlGeneratorInterface  $url_generator  The url generator service.
     */
    public function __construct(
        ActionManagerInterface $action_manager,
        UserProviderInterface $user_provider,
        UrlGeneratorInterface $url_generator
    ) {
        $this->action_manager = $action_manager;
        $this->user_provider = $user_provider;
        $this->url_generator = $url_generator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityPublishedEvent::NAME => [
                ['createInvitationTimelineEntries', 0],
                ['createScheduleTimelineEntry', 0],
            ],
        ];
    }

    public function createInvitationTimelineEntries(EntityPublishedEvent $published_event): void
    {
        // Only act on event objects.
        $event = $published_event->getObject();
        if (!$event instanceof Event) {
            return;
        }

        // Get the author for the event.
        $author = $this->user_provider->loadUserByUsername($event->getAuthor());

        // Create components for this action.
        $event_component = $this->action_manager->findOrCreateComponent($event);
        $author_component = $this->action_manager->findOrCreateComponent($author);

        // Add timeline entries for each group.
        foreach ($event->getGroups() as $group) {
            foreach ($event->getInvitations() as $invitation) {
                $invitee = $this->user_provider->loadUserByUsername($invitation->getInvitee());
                $invitee_component = $this->action_manager->findOrCreateComponent($invitee);

                // Create the action and link it.
                $action = $this->action_manager->create($author_component, 'invited', [
                    'directComplement' => $invitee_component,
                    'indirectComplement' => $event_component,
                ]);
                $action->setLink($this->url_generator->generate('bkstg_event_read', [
                    'production_slug' => $group->getSlug(),
                    'id' => $event->getId(),
                ]));

                // Update the action.
                $this->action_manager->updateAction($action);
            }
        }
    }

    public function createScheduleTimelineEntry(EntityPublishedEvent $event): void
    {
        // Only act on schedule objects.
        $schedule = $event->getObject();
        if (!$schedule instanceof Schedule) {
            return;
        }

        // Get the author for the schedule.
        $author = $this->user_provider->loadUserByUsername($schedule->getAuthor());

        // Create components for this action.
        $schedule_component = $this->action_manager->findOrCreateComponent($schedule);
        $author_component = $this->action_manager->findOrCreateComponent($author);

        // Add timeline entries for each group.
        foreach ($schedule->getGroups() as $group) {
            // Create the group component.
            $group_component = $this->action_manager->findOrCreateComponent($group);

            // Create the action and link it.
            $action = $this->action_manager->create($author_component, 'scheduled', [
                'directComplement' => $schedule_component,
                'indirectComplement' => $group_component,
            ]);
            $action->setLink($this->url_generator->generate('bkstg_schedule_read', [
                'production_slug' => $group->getSlug(),
                'id' => $schedule->getId(),
            ]));

            // Update the action.
            $this->action_manager->updateAction($action);
        }
    }
}
