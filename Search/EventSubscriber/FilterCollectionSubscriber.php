<?php

namespace Bkstg\ScheduleBundle\Search\EventSubscriber;

use Bkstg\SearchBundle\Event\FilterCollectionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FilterCollectionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FilterCollectionEvent::NAME => [
                ['addEventFilter', 0],
                ['addScheduleFilter', 0],
            ]
        ];
    }

    public function addEventFilter(FilterCollectionEvent $event)
    {
        $now = new \DateTime();
        $qb = $event->getQueryBuilder();
        $query = $qb->query()->bool()
            ->addMust($qb->query()->term(['_type' => 'event']))
            ->addMust($qb->query()->term(['active' => true]))
            ->addMust($qb->query()->terms('groups.id', $event->getGroupIds()))
            ->addMust($qb->query()->constant_score()->setParam('filter', ['missing' => ['field' => 'schedule']]))
        ;
        $event->addFilter($query);
    }

    public function addScheduleFilter(FilterCollectionEvent $event)
    {
        $now = new \DateTime();
        $qb = $event->getQueryBuilder();
        $query = $qb->query()->bool()
            ->addMust($qb->query()->term(['_type' => 'schedule']))
            ->addMust($qb->query()->term(['active' => true]))
            ->addMust($qb->query()->terms('groups.id', $event->getGroupIds()))
        ;
        $event->addFilter($query);
    }
}
