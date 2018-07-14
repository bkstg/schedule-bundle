<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\Repository;

use Bkstg\CoreBundle\Entity\Production;
use Doctrine\ORM\EntityRepository;

class ScheduleRepository extends EntityRepository
{
    /**
     * Build query to find archived schedules for a production.
     *
     * @param Production $production The production to search for schedules in.
     *
     * @return Schedule[]
     */
    public function findArchivedSchedulesQuery(Production $production)
    {
        $qb = $this->createQueryBuilder('s');

        return $qb
            // Add joins.
            ->join('s.groups', 'g')

            // Add conditions.
            ->andWhere($qb->expr()->eq('g', ':production'))
            ->andWhere($qb->expr()->eq('s.active', ':active'))

            // Add parameters.
            ->setParameter('production', $production)
            ->setParameter('active', false)

            // Add ordering.
            ->addOrderBy('s.published', 'ASC')
            ->addOrderBy('s.updated', 'DESC')

            // Get results.
            ->getQuery();
    }
}
