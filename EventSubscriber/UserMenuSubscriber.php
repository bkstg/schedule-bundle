<?php

namespace Bkstg\ScheduleBundle\EventSubscriber;

use Bkstg\CoreBundle\Event\UserMenuCollectionEvent;
use Bkstg\CoreBundle\Event\MenuCollectionEvent;
use Bkstg\ScheduleBundle\BkstgScheduleBundle;
use Knp\Menu\FactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserMenuSubscriber implements EventSubscriberInterface
{
    private $factory;

    public function __construct(FactoryInterface $factory) {
        $this->factory = $factory;
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
           UserMenuCollectionEvent::NAME => [
               ['addScheduleMenuItem', 0],
           ],
        ];
    }

    public function addScheduleMenuItem(MenuCollectionEvent $event)
    {
        $menu = $event->getMenu();

        $schedule = $this->factory->createItem('menu_item.my_schedule', [
            'route' => 'bkstg_calendar_personal',
            'extras' => [
                'icon' => 'calendar',
                'translation_domain' => BkstgScheduleBundle::TRANSLATION_DOMAIN,
            ],
        ]);
        $menu->addChild($schedule);
    }
}
