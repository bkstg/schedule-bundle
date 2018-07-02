<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgCoreBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

        if (!$object instanceof Event
            || 'schedule' != $action->getVerb()) {
            return false;
        }

        return true;
    }
}
