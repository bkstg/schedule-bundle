<?php

namespace Bkstg\ScheduleBundle\Security;

use Bkstg\CoreBundle\Security\GroupableEntityVoter;
use Bkstg\ScheduleBundle\Entity\Schedule;
use MidnightLuke\GroupSecurityBundle\Model\GroupableInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ScheduleVoter extends GroupableEntityVoter
{
    /**
     * {@inheritdoc}
     *
     * @param  mixed $attribute The attribute to vote on.
     * @param  mixed $subject   The subject to vote on.
     * @return boolean
     */
    protected function supports($attribute, $subject): boolean
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
