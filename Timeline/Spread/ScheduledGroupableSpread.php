<?php

namespace Bkstg\ScheduleBundle\Timeline\Spread;

use Bkstg\ScheduleBundle\Entity\Schedule;
use Bkstg\TimelineBundle\Spread\GroupableSpread;
use Spy\Timeline\Model\ActionInterface;

class ScheduledGroupableSpread extends GroupableSpread
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
