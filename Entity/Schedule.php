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

class Schedule implements GroupableInterface, PublishableInterface
{
    private $id;
    private $name;
    private $location;
    private $colour;
    private $description;
    private $active;
    private $published;
    private $author;
    private $created;
    private $updated;
    private $groups;
    private $events;

    /**
     * Create a new schedule.
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    /**
     * Get the id for the schedule.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * Set name.
     *
     * @param mixed $name The name.
     *
     * @return $this
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get location.
     *
     * @return ?string
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * Set location.
     *
     * @param string $location The location.
     *
     * @return self
     */
    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get colour.
     *
     * @return ?string
     */
    public function getColour(): ?string
    {
        return $this->colour;
    }

    /**
     * Set colour.
     *
     * @param string $colour The colour.
     *
     * @return $this
     */
    public function setColour(string $colour): self
    {
        $this->colour = $colour;

        return $this;
    }

    /**
     * Get description.
     *
     * @return ?string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param string $description The description.
     *
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

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
     * @param bool $active Whether this is active or not.
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
     * @param bool $published Whether or not this is published.
     *
     * @return self
     */
    public function setPublished(bool $published): PublishableInterface
    {
        $this->published = $published;

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
     * Set author.
     *
     * @param string $author The author.
     *
     * @return $this
     */
    public function setAuthor(string $author): self
    {
        $this->author = $author;

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
     * Set created.
     *
     * @param \DateTime $created The created date.
     *
     * @return $this
     */
    public function setCreated(\DateTime $created): self
    {
        $this->created = $created;

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
     * Set updated.
     *
     * @param \DateTime $updated The update time.
     *
     * @return self
     */
    public function setUpdated(\DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Add group.
     *
     * @param Production $group The group.
     *
     * @return self
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
     * @param Production $group The group to remove.
     *
     * @return void
     */
    public function removeGroup(GroupInterface $group): void
    {
        if (!$group instanceof Production) {
            throw new Exception('Group type not supported.');
        }
        $this->groups->removeElement($group);
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
     * @param GroupInterface $group The group.
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
     * Add event.
     *
     * @param Event $event The event to add.
     *
     * @return self
     */
    public function addEvent(Event $event): self
    {
        $event->setSchedule($this);
        $this->events[] = $event;

        return $this;
    }

    /**
     * Remove event.
     *
     * @param Event $event The event to remove.
     *
     * @return bool
     */
    public function removeEvent(Event $event)
    {
        return $this->events->removeElement($event);
    }

    /**
     * Get events.
     *
     * @return ArrayCollection
     */
    public function getEvents(): ArrayCollection
    {
        return $this->events;
    }

    /**
     * Get the start date of the schedule.
     *
     * @return ?\DateTimeInterface
     */
    public function getStart(): ?\DateTimeInterface
    {
        $lowest_date = null;
        foreach ($this->events as $event) {
            if (null === $lowest_date || $event->getStart() < $lowest_date) {
                $lowest_date = $event->getStart();
            }
        }

        return $lowest_date;
    }

    /**
     * Get the end date of the schedule.
     *
     * @return ?\DateTimeInterface
     */
    public function getEnd(): ?\DateTimeInterface
    {
        $highest_date = null;
        foreach ($this->events as $event) {
            if (null === $highest_date || $event->getEnd() > $highest_date) {
                $highest_date = $event->getEnd();
            }
        }

        return $highest_date;
    }

    /**
     * Return the string representation of the schedule.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
