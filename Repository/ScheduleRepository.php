<?php

namespace Bkstg\ScheduleBundle\Repository;

use Bkstg\CoreBundle\Entity\Production;
use Doctrine\ORM\EntityRepository;

class ScheduleRepository extends EntityRepository
{
    public function findArchivedSchedulesQuery(Production $production)
    {
        $qb = $this->createQueryBuilder('s');
        return $qb
            // Add conditions.
            ->andWhere($qb->expr()->eq('s.active', ':active'))

            // Add parameters.
            ->setParameter('active', false)

            // Add ordering.
            ->addOrderBy('s.published', 'ASC')
            ->addOrderBy('s.updated', 'DESC')

            // Get results.
            ->getQuery();
    }
}
