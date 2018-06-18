<?php

namespace Bkstg\ScheduleBundle\Repository;

use Bkstg\CoreBundle\User\UserInterface;
use Doctrine\ORM\EntityRepository;

class InvitationRepository extends EntityRepository
{
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

    public function findPendingInvitations(UserInterface $user)
    {
        return $this->findPendingInvitationsQuery($user)->getResult();
    }
}
