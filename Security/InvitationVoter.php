<?php

namespace Bkstg\ScheduleBundle\Security;

use Bkstg\CoreBundle\User\UserInterface;
use Bkstg\ScheduleBundle\Entity\Invitation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class InvitationVoter extends Voter
{
    const RESPOND = 'respond';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::RESPOND])) {
            return false;
        }

        // only vote on Groupable objects inside this voter
        if (!$subject instanceof Invitation) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        $invitation = $subject;

        switch ($attribute) {
            case self::RESPOND:
                return $user->getUsername() == $invitation->getInvitee();
        }
    }
}
