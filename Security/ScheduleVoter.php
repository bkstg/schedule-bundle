<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgCoreBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\Security;

use Bkstg\CoreBundle\Security\GroupableEntityVoter;
use Bkstg\ScheduleBundle\Entity\Schedule;

class ScheduleVoter extends GroupableEntityVoter
{
    /**
     * {@inheritdoc}
     *
     * @param mixed $attribute The attribute to vote on.
     * @param mixed $subject   The subject to vote on.
     *
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Schedule) {
            return false;
        }

        return true;
    }
}
