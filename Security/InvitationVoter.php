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
     *
     * @param  mixed $attribute The attribute to vote on.
     * @param  mixed $subject   The subject to vote on.
     * @return boolean
     */
    protected function supports($attribute, $subject): boolean
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
     *
     * @param  mixed          $attribute The attribute to vote on.
     * @param  mixed          $subject   The subject to vote on.
     * @param  TokenInterface $token     The user token.
     * @return boolean
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): boolean
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
