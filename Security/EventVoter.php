<?php

namespace Bkstg\ScheduleBundle\Security;

use Bkstg\CoreBundle\Security\GroupableEntityVoter;
use Bkstg\ScheduleBundle\Entity\Event;
use MidnightLuke\GroupSecurityBundle\Model\GroupableInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EventVoter extends GroupableEntityVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        // only vote on Groupable objects inside this voter
        if (!$subject instanceof Event) {
            return false;
        }

        return true;
    }

    public function canEdit(GroupableInterface $event, TokenInterface $token)
    {
        foreach ($event->getGroups() as $group) {
            if ($this->decision_manager->decide($token, ['GROUP_ROLE_EDITOR'], $group)) {
                return true;
            }
        }
        return false;
    }
}
