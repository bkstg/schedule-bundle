<?php

namespace Bkstg\ScheduleBundle\EventListener;

use Bkstg\CoreBundle\User\UserProviderInterface;
use Bkstg\ScheduleBundle\Entity\Invitation;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Spy\Timeline\Driver\ActionManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InvitationNotificationCreator
{
    private $action_manager;
    private $user_provider;
    private $url_genertor;

    /**
     * Contructor for invitation creator listener.
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
     * Listener for invitation creation that creates invitation timeline entry.
     *
     * @param LifecycleEventArgs $args The event arguments.
     * @return void
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        // Only act on invitations.
        $invitation = $args->getObject();
        if (!$invitation instanceof Invitation) {
            return;
        }

        // Get event, author and invitee.
        $event = $invitation->getEvent();
        $author = $this->user_provider->loadUserByUsername($event->getAuthor());
        $invitee = $this->user_provider->loadUserByUsername($invitation->getInvitee());

        // Create components.
        $event_component = $this->action_manager->findOrCreateComponent($event);
        $author_component = $this->action_manager->findOrCreateComponent($author);
        $invitee_component = $this->action_manager->findOrCreateComponent($invitee);

        // Iterate over groups and create components.
        foreach ($event->getGroups() as $group) {
            // If this invitation is not part of a schedule create timeline.
            $action = $this->action_manager->create(
                $invitee_component,
                'invite',
                ['directComplement' => $event_component, 'indirectComplement' => $author_component]
            );
            $action->setLink($this->url_generator->generate(
                'bkstg_event_show',
                ['id' => $event->getId(), 'production_slug' => $group->getSlug()]
            ));

            // Update the action.
            $this->action_manager->updateAction($action);
        }
    }
}
