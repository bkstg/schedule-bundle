<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgCoreBundle package.
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
    private $title;
    private $location;
    private $colour;
    private $notes;
    private $active;
    private $published;
    private $author;
    private $created;
    private $updated;
    private $groups;
    private $events;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Get title.
     *
     * @return
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get location.
     *
     * @return
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * Set location.
     *
     * @return $this
     */
    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get colour.
     *
     * @return
     */
    public function getColour(): ?string
    {
        return $this->colour;
    }

    /**
     * Set colour.
     *
     * @return $this
     */
    public function setColour(string $colour): self
    {
        $this->colour = $colour;

        return $this;
    }

    /**
     * Get notes.
     *
     * @return
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set notes.
     *
     * @return $this
     */
    public function setNotes(string $notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get active.
     *
     * @return
     */
    public function isActive(): bool
    {
        return true === $this->active;
    }

    /**
     * Set active.
     *
     * @return $this
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get published.
     *
     * @return
     */
    public function isPublished(): bool
    {
        return true === $this->published;
    }

    /**
     * Set published.
     *
     * @return $this
     */
    public function setPublished(bool $published): PublishableInterface
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get author.
     *
     * @return
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set author.
     *
     * @return $this
     */
    public function setAuthor(string $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get created.
     *
     * @return
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created.
     *
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated.
     *
     * @return $this
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Add group.
     *
     * @param Production $group
     *
     * @return Post
     */
    public function addGroup(GroupInterface $group)
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
     * @param Production $group
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
     * @return Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
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
     * @param Event $event
     *
     * @return Schedule
     */
    public function addEvent(Event $event)
    {
        $event->setSchedule($this);
        $this->events[] = $event;

        return $this;
    }

    /**
     * Remove event.
     *
     * @param Event $event
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeEvent(Event $event)
    {
        return $this->events->removeElement($event);
    }

    /**
     * Get events.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

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
}
