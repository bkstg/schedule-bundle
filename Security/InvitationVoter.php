<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @param mixed $attribute The attribute to vote on.
     * @param mixed $subject   The subject to vote on.
     *
     * @return bool
     */
    protected function supports($attribute, $subject): bool
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
     * @param mixed          $attribute The attribute to vote on.
     * @param mixed          $subject   The subject to vote on.
     * @param TokenInterface $token     The user token.
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
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
