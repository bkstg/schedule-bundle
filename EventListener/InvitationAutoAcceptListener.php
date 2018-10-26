<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\EventListener;

use Bkstg\ScheduleBundle\Entity\Invitation;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class InvitationAutoAcceptListener
{
    /**
     * Checks the invitation author and accepts if they match.
     *
     * @param LifecycleEventArgs $args The lifecycle arguments.
     *
     * @return void
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
