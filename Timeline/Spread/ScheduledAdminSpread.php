<?php

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
