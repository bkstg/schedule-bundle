<?php

namespace Bkstg\ScheduleBundle\Spread;

use Bkstg\ScheduleBundle\Entity\Event;
use Bkstg\TimelineBundle\Spread\GroupableSpread;
use Spy\Timeline\Model\ActionInterface;

class EventGroupableSpread extends GroupableSpread
{
    /**
     * {@inheritdoc}
     */
    public function supports(ActionInterface $action)
    {
        $object = $action->getComponent('directComplement')->getData();

        if (!$object instanceof Event) {
            return false;
        }

        return true;
    }
}
