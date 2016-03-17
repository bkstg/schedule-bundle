<?php

namespace Bkstg\ScheduleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Schedule
 *
 * @ORM\Table(name="schedules")
 * @ORM\Entity
 */
class Schedule
{
    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Bkstg\ScheduleBundle\Entity\ScheduleItem",
     *     mappedBy="schedule",
     *     cascade={"persist", "remove"}
     * )
     */
    private $scheduleItems;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="changed", type="datetime")
     */
    private $changed;

    /**
     * @ORM\ManyToOne(targetEntity="Bkstg\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->scheduleItems = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Schedule
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add scheduleItems
     *
     * @param \Bkstg\ScheduleBundle\Entity\ScheduleItem $scheduleItems
     * @return Schedule
     */
    public function addScheduleItem(\Bkstg\ScheduleBundle\Entity\ScheduleItem $scheduleItems)
    {
        $this->scheduleItems[] = $scheduleItems;

        // enforce parent schedule on new item
        $scheduleItems->setSchedule($this);

        return $this;
    }

    /**
     * Remove scheduleItems
     *
     * @param \Bkstg\ScheduleBundle\Entity\ScheduleItem $scheduleItems
     */
    public function removeScheduleItem(\Bkstg\ScheduleBundle\Entity\ScheduleItem $scheduleItems)
    {
        $this->scheduleItems->removeElement($scheduleItems);
    }

    /**
     * Get scheduleItems
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getScheduleItems()
    {
        return $this->scheduleItems;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Schedule
     */
    public function setStatus($status)
    {
        if(!in_array($status, array(self::STATUS_ACTIVE, self::STATUS_DRAFT))) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Schedule
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set changed
     *
     * @param \DateTime $changed
     * @return Schedule
     */
    public function setChanged($changed)
    {
        $this->changed = $changed;

        return $this;
    }

    /**
     * Get changed
     *
     * @return \DateTime
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * Set user
     *
     * @param \Bkstg\CoreBundle\Entity\User $user
     * @return Schedule
     */
    public function setUser(\Bkstg\CoreBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Bkstg\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
