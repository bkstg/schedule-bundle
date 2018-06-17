<?php

namespace Bkstg\ScheduleBundle\Repository;

use Bkstg\CoreBundle\Entity\Production;
use Bkstg\CoreBundle\User\UserInterface;
use Bkstg\ScheduleBundle\Entity\Event;
use Bkstg\ScheduleBundle\Entity\Invitation;
use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository
{
    public function searchEvents(
        Production $production,
        \DateTime $from,
        \DateTime $to,
        bool $active = true
    ) {
        $qb = $this->createQueryBuilder('e');
        return $qb
            ->join('e.groups', 'g')

            // Add conditions.
            ->andWhere($qb->expr()->eq('g', ':group'))
            ->andWhere($qb->expr()->between('e.start', ':from', ':to'))
            ->andWhere($qb->expr()->eq('e.active', ':active'))

            // Add parameters.
            ->setParameter('group', $production)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('active', $active)

            // Get results.
            ->getQuery()
            ->getResult();
    }

    public function searchEventsByUser(
        Production $production,
        UserInterface $user,
        \DateTime $from,
        \DateTime $to
        bool $active = true
    ) {
        $qb = $this->createQueryBuilder('e');
        return $qb
            ->join('e.groups', 'g')
            ->join('e.invitations', 'i')

            // Add conditions.
            ->andWhere($qb->expr()->eq('g', ':group'))
            ->andWhere($qb->expr()->between('e.start', ':from', ':to'))
            ->andWhere($qb->expr()->eq('i.invitee', ':invitee'))
            ->andWhere($qb->expr()->eq('e.active', ':active'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull('i.response'),
                $qb->expr()->neq('i.response', ':decline')
            ))

            // Add parameters.
            ->setParameter('group', $production)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('invitee', $user->getUsername())
            ->setParameter('decline', Invitation::RESPONSE_DECLINE)
            ->setParameter('active', $active)

            // Get results.
            ->getQuery()
            ->getResult();
    }
}
