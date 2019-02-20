<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\Entity;

use Bkstg\CoreBundle\Entity\Production;
use Bkstg\CoreBundle\Model\PublishableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use MidnightLuke\GroupSecurityBundle\Model\GroupableInterface;
use MidnightLuke\GroupSecurityBundle\Model\GroupInterface;

class Event implements GroupableInterface, PublishableInterface
{
    private $id;
    private $start;
    private $end;
    private $name;
    private $description;
    private $location;
    private $created;
    private $updated;
    private $author;
    private $groups;
    private $invitations;
    private $colour;
    private $full_company;
    private $active;
    private $published;
    private $schedule;

    /**
     * Create a new event.
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->invitations = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set start.
     *
     * @param \DateTime $start The start date.
     *
     * @return Event
     */
    public function setStart(\DateTime $start): self
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start.
     *
     * @return \DateTime
     */
    public function getStart(): \DateTime
    {
        return $this->start;
    }

    /**
     * Set end.
     *
     * @param \DateTime $end The end date.
     *
     * @return Event
     */
    public function setEnd(\DateTime $end): self
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end.
     *
     * @return \DateTime
     */
    public function getEnd(): \DateTime
    {
        return $this->end;
    }

    /**
     * Set name.
     *
     * @param string $name The event name.
     *
     * @return Event
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set description.
     *
     * @param string|null $description The description.
     *
     * @return Event
     */
    public function setDescription(string $description = null): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set location.
     *
     * @param string|null $location The location.
     *
     * @return Event
     */
    public function setLocation(string $location = null): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created The created time.
     *
     * @return Event
     */
    public function setCreated(\DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated The updated time.
     *
     * @return Event
     */
    public function setUpdated(\DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    /**
     * Set author.
     *
     * @param string $author The author.
     *
     * @return Event
     */
    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Add group.
     *
     * @param Production $group The production.
     *
     * @return Event
     */
    public function addGroup(GroupInterface $group): self
    {
        if (!$group instanceof Production) {
            throw new Exception('Group type not supported.');
        }
        $this->groups[] = $group;

        return $this;
    }

    /**
     * Remove group.
     *
     * @param Production $group The production.
     *
     * @return bool
     */
    public function removeGroup(GroupInterface $group): bool
    {
        return $this->groups->removeElement($group);
    }

    /**
     * Get groups.
     *
     * @return ArrayCollection
     */
    public function getGroups(): ArrayCollection
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     *
     * @param GroupInterface $group The group to check for.
     *
     * @return bool
     */
    public function hasGroup(GroupInterface $group): bool
    {
        foreach ($this->groups as $my_group) {
            if ($group->isEqualTo($my_group)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add invitation.
     *
     * @param Invitation $invitation The invitation.
     *
     * @return Event
     */
    public function addInvitation(Invitation $invitation): self
    {
        $invitation->setEvent($this);
        $this->invitations[] = $invitation;

        return $this;
    }

    /**
     * Remove invitation.
     *
     * @param Invitation $invitation The invitation.
     *
     * @return bool
     */
    public function removeInvitation(Invitation $invitation): bool
    {
        return $this->invitations->removeElement($invitation);
    }

    /**
     * Get invitations.
     *
     * @return ArrayCollection
     */
    public function getInvitations(): ArrayCollection
    {
        return $this->invitations;
    }

    /**
     * Set schedule.
     *
     * @param Schedule|null $schedule The schedule.
     *
     * @return Event
     */
    public function setSchedule(Schedule $schedule = null): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Get schedule.
     *
     * @return Schedule|null
     */
    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    /**
     * Get colour.
     *
     * @return self
     */
    public function getColour(): ?string
    {
        return $this->colour;
    }

    /**
     * Set colour.
     *
     * @param ?string $colour The colour.
     *
     * @return $this
     */
    public function setColour(?string $colour): self
    {
        $this->colour = $colour;

        return $this;
    }

    /**
     * Get full_company.
     *
     * @return bool
     */
    public function getFullCompany(): ?bool
    {
        return $this->full_company;
    }

    /**
     * Set full_company.
     *
     * @param bool $full_company Whether this is a full company call or not.
     *
     * @return $this
     */
    public function setFullCompany(bool $full_company)
    {
        $this->full_company = $full_company;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return true === $this->active;
    }

    /**
     * Set active.
     *
     * @param bool $active Whether this active or not.
     *
     * @return self
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get published.
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return true === $this->published;
    }

    /**
     * Set published.
     *
     * @param bool $published Whether this is published or not.
     *
     * @return $this
     */
    public function setPublished(bool $published): PublishableInterface
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Return string represenation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
