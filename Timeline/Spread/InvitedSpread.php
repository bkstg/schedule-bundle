<?php

namespace Bkstg\ScheduleBundle\Timeline\Spread;

use Doctrine\Common\Proxy\Proxy;
use Doctrine\ORM\EntityManagerInterface;
use Spy\Timeline\Model\ActionInterface;
use Spy\Timeline\Spread\Entry\EntryCollection;
use Spy\Timeline\Spread\Entry\EntryUnaware;
use Spy\Timeline\Spread\SpreadInterface;

class InvitedSpread implements SpreadInterface
{
    private $em;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    /**
     * You spread class is support the action ?
     *
     * @param ActionInterface $action
     *
     * @return boolean
     */
    public function supports(ActionInterface $action): bool
    {
        if ($action->getVerb() != 'invited') {
            return false;
        }
        return true;
    }

    /**
     * @param  ActionInterface $action action we look for spreads
     * @param  EntryCollection $coll   Spreads defined on an EntryCollection
     */
    public function process(ActionInterface $action, EntryCollection $collection): void
    {
        $invitee = $action->getComponent('directComplement')->getData();
        $collection->add(new EntryUnaware($this->resolveClass($invitee), $invitee->getId()));
    }

    private function resolveClass($object) {
        if (!$object instanceof Proxy) {
            return get_class($object);
        }

        // If this is a proxy resolve using class metadata.
        return $this
            ->em
            ->getClassMetadata(get_class($object))
            ->getReflectionClass()
            ->getName();
    }
}
