<?php

namespace Bkstg\ScheduleBundle\Entity;

use Bkstg\CoreBundle\Entity\Production;
use Bkstg\ScheduleBundle\Entity\Invitation;
use Bkstg\SearchBundle\Model\SearchableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use MidnightLuke\GroupSecurityBundle\Model\GroupInterface;
use MidnightLuke\GroupSecurityBundle\Model\GroupableInterface;

/**
 * Event
 */
class Event implements SearchableInterface
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
    private $type;
    private $full_company;
    private $status;

    /**
     * Constructor
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set start.
     *
     * @param \DateTime $start
     *
     * @return Event
     */
    public function setStart(\DateTime $start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start.
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end.
     *
     * @param \DateTime $end
     *
     * @return Event
     */
    public function setEnd(\DateTime $end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end.
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Event
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return Event
     */
    public function setDescription(string $description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set location.
     *
     * @param string|null $location
     *
     * @return Event
     */
    public function setLocation(string $location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Event
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return Event
     */
    public function setUpdated(\DateTime $updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set author.
     *
     * @param string $author
     *
     * @return Event
     */
    public function setAuthor(string $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Add group.
     *
     * @param Production $group
     *
     * @return Event
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
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeGroup(GroupInterface $group)
    {
        return $this->groups->removeElement($group);
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
    public function hasGroup(GroupInterface $group)
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
     * @param Invitation $invitation
     *
     * @return Event
     */
    public function addInvitation(Invitation $invitation)
    {
        $invitation->setEvent($this);
        $this->invitations[] = $invitation;

        return $this;
    }

    /**
     * Remove invitation.
     *
     * @param Invitation $invitation
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeInvitation(Invitation $invitation)
    {
        return $this->invitations->removeElement($invitation);
    }

    /**
     * Get invitations.
     *
     * @return Collection
     */
    public function getInvitations()
    {
        return $this->invitations;
    }
    /**
     * @var \Bkstg\ScheduleBundle\Entity\Schedule
     */
    private $schedule;


    /**
     * Set schedule.
     *
     * @param \Bkstg\ScheduleBundle\Entity\Schedule|null $schedule
     *
     * @return Event
     */
    public function setSchedule(\Bkstg\ScheduleBundle\Entity\Schedule $schedule = null)
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Get schedule.
     *
     * @return \Bkstg\ScheduleBundle\Entity\Schedule|null
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * Get type
     * @return
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get full_company
     * @return
     */
    public function getFullCompany()
    {
        return $this->full_company;
    }

    /**
     * Set full_company
     * @return $this
     */
    public function setFullCompany(bool $full_company)
    {
        $this->full_company = $full_company;
        return $this;
    }

    /**
     * Get status
     * @return
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     * @return $this
     */
    public function setStatus(int $status)
    {
        $this->status = $status;
        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}
