<?php

namespace Bkstg\ScheduleBundle\EventListener;

use Bkstg\CoreBundle\User\MembershipProviderInterface;
use Bkstg\ScheduleBundle\Entity\Event;
use Bkstg\ScheduleBundle\Entity\Invitation;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class FullCompanyListener
{
    private $membership_provider;

    public function __construct(MembershipProviderInterface $membership_provider)
    {
        $this->membership_provider = $membership_provider;
    }


    /**
     * When a user creates a production make them an admin in the production.
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        // Only act on "Event" entities.
        if (!$object instanceof Event) {
            return;
        }

        $om = $args->getObjectManager();

        // Create and persist invitations for all active members of groups.
        foreach ($object->getGroups() as $group) {
            foreach ($this->membership_provider->loadMembershipsByGroup($group) as $membership) {
                if ($membership->isActive() && !$membership->isExpired()) {
                    $invite = new Invitation();
                    $invite->setEvent($object);
                    $invite->setInvitee($membership->getMember()->getUsername());
                    $invite->setOptional(false);
                    $om->persist($invite);
                }
            }
        }
    }
}
