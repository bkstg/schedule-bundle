<?php

namespace Bkstg\ScheduleBundle\Repository;

use Bkstg\CoreBundle\Entity\Production;
use Bkstg\ScheduleBundle\Entity\Event;
use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository
{
    public function searchEvents(
        Production $production,
        \DateTime $from,
        \DateTime $to
    ) {
        $qb = $this->createQueryBuilder('e');
        return $qb
            ->join('e.groups', 'g')

            // Add conditions.
            ->andWhere($qb->expr()->eq('g', ':group'))
            ->andWhere($qb->expr()->between('e.start', ':from', ':to'))

            // Add parameters.
            ->setParameter('group', $production)
            ->setParameter('from', $from)
            ->setParameter('to', $to)

            // Get results.
            ->getQuery()
            ->getResult();
    }
}
