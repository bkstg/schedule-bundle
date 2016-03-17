<?php

namespace Bkstg\ScheduleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScheduleItem
 *
 * @ORM\Table(name="schedule_items")
 * @ORM\Entity
 */
class ScheduleItem
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datetime_start", type="datetime")
     */
    private $datetimeStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datetime_end", type="datetime")
     */
    private $datetimeEnd;

    /**
     * @ORM\ManyToMany(targetEntity="Bkstg\CoreBundle\Entity\User")
     * @ORM\JoinTable(name="users_called",
     *      joinColumns={@ORM\JoinColumn(name="schedule_item_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     */
    private $called;

    /**
     * @var string
     *
     * @ORM\Column(name="scene", type="text")
     */
    private $scene;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Bkstg\ScheduleBundle\Entity\Schedule",
     *     inversedBy="scheduleItems"
     * )
     * @ORM\JoinColumn(name="schedule_id", referencedColumnName="id", nullable=false)
     */
    private $schedule;

    /**
     * @ORM\Column(name="full_company", type="boolean")
     */
    private $fullCompany;

    /**
     * @ORM\Column(name="full_cast", type="boolean")
     */
    private $fullCast;

    /**
     * @ORM\Column(name="full_crew", type="boolean")
     */
    private $fullCrew;

    public function __construct()
    {
        $this->called = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set datetimeStart
     *
     * @param \DateTime $datetimeStart
     * @return ScheduleItem
     */
    public function setDatetimeStart($datetimeStart)
    {
        $this->datetimeStart = $datetimeStart;

        return $this;
    }

    /**
     * Get datetimeStart
     *
     * @return \DateTime
     */
    public function getDatetimeStart()
    {
        return $this->datetimeStart;
    }

    /**
     * Set datetimeEnd
     *
     * @param \DateTime $datetimeEnd
     * @return ScheduleItem
     */
    public function setDatetimeEnd($datetimeEnd)
    {
        $this->datetimeEnd = $datetimeEnd;

        return $this;
    }

    /**
     * Get datetimeEnd
     *
     * @return \DateTime
     */
    public function getDatetimeEnd()
    {
        return $this->datetimeEnd;
    }

    /**
     * Set scene
     *
     * @param string $scene
     * @return ScheduleItem
     */
    public function setScene($scene)
    {
        $this->scene = $scene;

        return $this;
    }

    /**
     * Get scene
     *
     * @return string
     */
    public function getScene()
    {
        return $this->scene;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return ScheduleItem
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Add called
     *
     * @param \Bkstg\CoreBundle\Entity\User $called
     * @return ScheduleItem
     */
    public function addCalled(\Bkstg\CoreBundle\Entity\User $called)
    {
        $this->called[] = $called;

        return $this;
    }

    /**
     * Remove called
     *
     * @param \Bkstg\CoreBundle\Entity\User $called
     */
    public function removeCalled(\Bkstg\CoreBundle\Entity\User $called)
    {
        $this->called->removeElement($called);
    }

    /**
     * Get called
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCalled()
    {
        return $this->called;
    }

    /**
     * Set schedule
     *
     * @param \Bkstg\ScheduleBundle\Entity\Schedule $schedule
     * @return ScheduleItem
     */
    public function setSchedule(\Bkstg\ScheduleBundle\Entity\Schedule $schedule = null)
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Get schedule
     *
     * @return \Bkstg\ScheduleBundle\Entity\Schedule
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * Set fullCompany
     *
     * @param boolean $fullCompany
     * @return ScheduleItem
     */
    public function setFullCompany($fullCompany)
    {
        $this->fullCompany = $fullCompany;

        return $this;
    }

    /**
     * Get fullCompany
     *
     * @return boolean
     */
    public function isFullCompany()
    {
        return $this->fullCompany;
    }

    /**
     * Set fullCast
     *
     * @param boolean $fullCast
     * @return ScheduleItem
     */
    public function setFullCast($fullCast)
    {
        $this->fullCast = $fullCast;

        return $this;
    }

    /**
     * Get fullCast
     *
     * @return boolean
     */
    public function isFullCast()
    {
        return $this->fullCast;
    }

    /**
     * Set fullCrew
     *
     * @param boolean $fullCrew
     * @return ScheduleItem
     */
    public function setFullCrew($fullCrew)
    {
        $this->fullCrew = $fullCrew;

        return $this;
    }

    /**
     * Get fullCrew
     *
     * @return boolean
     */
    public function isFullCrew()
    {
        return $this->fullCrew;
    }
}
