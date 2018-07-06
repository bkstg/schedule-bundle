<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgCoreBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\EventListener;

use Bkstg\ScheduleBundle\Entity\Event;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Spy\Timeline\Driver\ActionManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Doctrine event listener for event creation.
 */
class EventNotificationCreator
{
    private $action_manager;
    private $user_provider;
    private $url_genertor;

    /**
     * Contructor for event creator listener.
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

    /**
     * Listener for event creation that creates invitation timeline entry.
     *
     * @param LifecycleEventArgs $args The event arguments.
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        // Only act on events.
        $event = $args->getObject();
        if (!$event instanceof Event) {
            return;
        }

        // Get the author for the event.
        $author = $this->user_provider->loadUserByUsername($event->getAuthor());

        // Create event and author component.
        $event_component = $this->action_manager->findOrCreateComponent($event);
        $author_component = $this->action_manager->findOrCreateComponent($author);

        // Iterate over groups and create components.
        foreach ($event->getGroups() as $group) {
            $group_component = $this->action_manager->findOrCreateComponent($group);

            // If this event is not part of a schedule create timeline.
            if (null === $event->getSchedule()) {
                $action = $this->action_manager->create(
                    $author_component,
                    'schedule',
                    ['directComplement' => $event_component, 'indirectComplement' => $group_component]
                );
                $action->setLink($this->url_generator->generate(
                    'bkstg_event_read',
                    ['id' => $event->getId(), 'production_slug' => $group->getSlug()]
                ));

                // Update the action.
                $this->action_manager->updateAction($action);
            }
        }
    }
}
