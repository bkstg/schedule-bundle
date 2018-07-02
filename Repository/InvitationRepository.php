<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgCoreBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\Repository;

use Bkstg\CoreBundle\User\UserInterface;
use Doctrine\ORM\EntityRepository;

class InvitationRepository extends EntityRepository
{
    /**
     * Build query to find pending invitations for a user.
     *
     * @param UserInterface $user The user to find invitations for.
     *
     * @return Invitation[]
     */
    public function findPendingInvitationsQuery(UserInterface $user)
    {
        $qb = $this->createQueryBuilder('i');

        return $qb
            ->join('i.event', 'e')

            // Add conditions.
            ->andWhere($qb->expr()->eq('e.active', ':active'))
            ->andWhere($qb->expr()->gt('e.end', ':now'))
            ->andWhere($qb->expr()->isNull('i.response'))
            ->andWhere($qb->expr()->eq('i.invitee', ':invitee'))

            // Add parameters.
            ->setParameter('active', true)
            ->setParameter('now', new \DateTime())
            ->setParameter('invitee', $user->getUsername())

            // Get query.
            ->getQuery();
    }

    /**
     * Find pending invitations for a user.
     *
     * @param UserInterface $user The user to find invitations for.
     *
     * @return Invitation[]
     */
    public function findPendingInvitations(UserInterface $user)
    {
        return $this->findPendingInvitationsQuery($user)->getResult();
    }

    /**
     * Build query to find other invitations for a user.
     *
     * @param UserInterface $user The user to find invitations for.
     *
     * @return Invitation[]
     */
    public function findOtherInvitationsQuery(UserInterface $user)
    {
        $qb = $this->createQueryBuilder('i');

        return $qb
            ->join('i.event', 'e')

            // Add conditions.
            ->andWhere($qb->expr()->eq('e.active', ':active'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->lt('e.end', ':now'),
                $qb->expr()->isNotNull('i.response')
            ))
            ->andWhere($qb->expr()->eq('i.invitee', ':invitee'))

            // Add parameters.
            ->setParameter('active', true)
            ->setParameter('now', new \DateTime())
            ->setParameter('invitee', $user->getUsername())

            // Add order by.
            ->addOrderBy('e.end', 'DESC')

            // Get query.
            ->getQuery();
    }

    /**
     * Find other invitations for a user.
     *
     * @param UserInterface $user The user to find invitations for.
     *
     * @return Invitation[]
     */
    public function findOtherInvitations(UserInterface $user)
    {
        return $this->findOtherInvitationsQuery($user)->getResult();
    }
}
