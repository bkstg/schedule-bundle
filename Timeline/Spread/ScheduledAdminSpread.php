<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\Timeline\Spread;

use Bkstg\ScheduleBundle\Entity\Schedule;
use Bkstg\TimelineBundle\Spread\AdminSpread;
use Spy\Timeline\Model\ActionInterface;

class ScheduledAdminSpread extends AdminSpread
{
    public function supports(ActionInterface $action)
    {
        $schedule = $action->getComponent('directComplement')->getData();
        if (!$schedule instanceof Schedule) {
            return false;
        }

        return true;
    }
}
