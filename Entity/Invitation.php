<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\Entity;

/**
 * Invitation.
 */
class Invitation
{
    const RESPONSE_ACCEPT = 1;
    const RESPONSE_MAYBE = 0;
    const RESPONSE_DECLINE = -1;

    private $id;
    private $response;
    private $optional;
    private $invitee;
    private $event;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set response.
     *
     * @param int|null $response
     *
     * @return Invitation
     */
    public function setResponse(int $response = null)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get response.
     *
     * @return int|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set optional.
     *
     * @param bool $optional
     *
     * @return Invitation
     */
    public function setOptional(bool $optional)
    {
        $this->optional = $optional;

        return $this;
    }

    /**
     * Get optional.
     *
     * @return bool
     */
    public function getOptional()
    {
        return $this->optional;
    }

    /**
     * Set invitee.
     *
     * @param string $invitee
     *
     * @return Invitation
     */
    public function setInvitee(string $invitee)
    {
        $this->invitee = $invitee;

        return $this;
    }

    /**
     * Get invitee.
     *
     * @return string
     */
    public function getInvitee()
    {
        return $this->invitee;
    }

    /**
     * Set event.
     *
     * @param Event|null $event
     *
     * @return Invitation
     */
    public function setEvent(Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event.
     *
     * @return Event|null
     */
    public function getEvent()
    {
        return $this->event;
    }
}
