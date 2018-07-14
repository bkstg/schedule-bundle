<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\EventListener;

use Bkstg\CoreBundle\User\MembershipProviderInterface;
use Bkstg\CoreBundle\User\UserInterface;
use Bkstg\ScheduleBundle\Entity\Event;
use Bkstg\ScheduleBundle\Entity\Invitation;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * Listens for new/updated events to create invitations for full company calls.
 */
class FullCompanyListener
{
    private $membership_provider;

    /**
     * Create a new FullCompanyListener.
     *
     * @param MembershipProviderInterface $membership_provider The membership provider service.
     */
    public function __construct(MembershipProviderInterface $membership_provider)
    {
        $this->membership_provider = $membership_provider;
    }

    /**
     * Listens for the onFlush event.
     *
     * @param OnFlushEventArgs $args The arguments for this event.
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        // Get the entity manager and unit of work.
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        // Check insertions for new events.
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            foreach ($this->getInvitations($entity) as $invitation) {
                $em->persist($invitation);
                $uow->computeChangeset($em->getClassMetadata(Invitation::class), $invitation);
            }
        }

        // Check updates for updated events.
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            foreach ($this->getInvitations($entity) as $invitation) {
                $em->persist($invitation);
                $uow->computeChangeset($em->getClassMetadata(Invitation::class), $invitation);
            }
        }
    }

    /**
     * Helper function that generates new invitations as needed.
     *
     * @param mixed $object The entity being acted on.
     *
     * @return Invitation[] The new invitations needed for this event.
     */
    private function getInvitations($object)
    {
        // This must be an event with a full company call.
        if (!$object instanceof Event
            || !$object->getFullCompany()) {
            return [];
        }

        // Index existing invites so that we don't duplicate any.
        $existing = [];
        foreach ($object->getInvitations() as $existing_invitation) {
            $existing[] = $existing_invitation->getInvitee();
        }

        // Create and persist invitations for all active members of groups.
        $invitations = [];
        foreach ($object->getGroups() as $group) {
            foreach ($this->membership_provider->loadActiveMembershipsByProduction($group) as $membership) {
                // We can only act on our users.
                if (!$membership->getMember() instanceof UserInterface) {
                    continue;
                }

                // If this membership is active, has not expired and is not
                // already invited create new invitation.
                if ($membership->isActive()
                    && !$membership->isExpired()
                    && !in_array($membership->getMember()->getUsername(), $existing)
                ) {
                    // Create new invitation to this event for this member.
                    $invite = new Invitation();
                    $invite->setEvent($object);
                    $invite->setInvitee($membership->getMember()->getUsername());
                    $invite->setOptional(false);
                    $invitations[] = $invite;
                }
            }
        }

        // Return the new invitations for this event.
        return $invitations;
    }
}
