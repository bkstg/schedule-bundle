<?php

namespace Bkstg\ScheduleBundle\EventListener;

use Bkstg\ScheduleBundle\Entity\Invitation;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class InvitationAutoAcceptListener
{
    /**
     * Checks the invitation author and accepts if they match.
     *
     * @param  LifecycleEventArgs $args The lifecycle arguments.
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        // Only act on invitation objects.
        $invitation = $args->getObject();
        if (!$invitation instanceof Invitation) {
            return;
        }

        // If the event author is the invitee accept the invitation.
        $event = $invitation->getEvent();
        if ($event->getAuthor() == $invitation->getInvitee()) {
            $invitation->setResponse(Invitation::RESPONSE_ACCEPT);
        }
    }
}
