<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    /**
     * Create a new invited spread.
     *
     * @param EntityManagerInterface $em The entity manager.
     */
    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     *
     * @param ActionInterface $action The action.
     *
     * @return bool
     */
    public function supports(ActionInterface $action): bool
    {
        if ('invited' != $action->getVerb()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param ActionInterface $action     Action to spread.
     * @param EntryCollection $collection Spreads defined on an EntryCollection.
     *
     * @return void
     */
    public function process(ActionInterface $action, EntryCollection $collection): void
    {
        $invitee = $action->getComponent('directComplement')->getData();
        $collection->add(new EntryUnaware($this->resolveClass($invitee), $invitee->getId()));
    }

    /**
     * Figure out the real class for this object.
     *
     * @param mixed $object The object.
     *
     * @return string
     */
    private function resolveClass($object)
    {
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
