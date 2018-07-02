<?php

namespace Bkstg\ScheduleBundle\Security;

use Bkstg\CoreBundle\Security\GroupableEntityVoter;
use Bkstg\ScheduleBundle\Entity\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EventVoter extends GroupableEntityVoter
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

        if (!$subject instanceof Event) {
            return false;
        }

        return true;
    }
}
