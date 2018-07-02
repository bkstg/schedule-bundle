<?php

namespace Bkstg\ScheduleBundle\Repository;

use Bkstg\CoreBundle\Entity\Production;
use Bkstg\CoreBundle\User\UserInterface;
use Bkstg\ScheduleBundle\Entity\Event;
use Bkstg\ScheduleBundle\Entity\Invitation;
use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository
{
    /**
     * Search for events within a production.
     *
     * @param  Production $production The production to search in.
     * @param  \DateTime  $from       The time to start searching from.
     * @param  \DateTime  $to         The time to end searching from.
     * @param  boolean    $active     The active state of the events.
     * @return Event[]
     */
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

    /**
     * Search for events for a user.
     *
     * @param  UserInterface $user   The production to search in.
     * @param  \DateTime     $from   The time to start searching from.
     * @param  \DateTime     $to     The time to end searching from.
     * @param  boolean       $active The active state of the events.
     * @return Event[]
     */
    public function searchEventsByUser(
        UserInterface $user,
        \DateTime $from,
        \DateTime $to,
        bool $active = true
    ) {
        $qb = $this->createQueryBuilder('e');
        return $qb
            ->join('e.invitations', 'i')

            // Add conditions.
            ->andWhere($qb->expr()->between('e.start', ':from', ':to'))
            ->andWhere($qb->expr()->eq('i.invitee', ':invitee'))
            ->andWhere($qb->expr()->eq('e.active', ':active'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull('i.response'),
                $qb->expr()->neq('i.response', ':decline')
            ))

            // Add parameters.
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('invitee', $user->getUsername())
            ->setParameter('decline', Invitation::RESPONSE_DECLINE)
            ->setParameter('active', $active)

            // Get results.
            ->getQuery()
            ->getResult();
    }

    /**
     * Helper function to search for events that are not active.
     *
     * @param  Production $production The production to search in.
     * @return Event[]
     */
    public function findArchivedEventsQuery(Production $production)
    {
        $qb = $this->createQueryBuilder('e');
        return $qb
            // Add conditions.
            ->andWhere($qb->expr()->eq('e.active', ':active'))
            ->andWhere($qb->expr()->isNull('e.schedule'))

            // Add parameters.
            ->setParameter('active', false)

            // Add ordering.
            ->addOrderBy('e.updated', 'DESC')

            // Get query.
            ->getQuery();
    }
}
