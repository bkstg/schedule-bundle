<?php

namespace Bkstg\ScheduleBundle\EventSubscriber;

use Bkstg\CoreBundle\Event\ProductionMenuCollectionEvent;
use Bkstg\ScheduleBundle\BkstgScheduleBundle;
use Knp\Menu\FactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductionMenuSubscriber implements EventSubscriberInterface
{
    private $factory;
    private $auth;

    public function __construct(
        FactoryInterface $factory,
        AuthorizationCheckerInterface $auth
    ) {
        $this->factory = $factory;
        $this->auth = $auth;
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
           ProductionMenuCollectionEvent::NAME => array(
               array('addScheduleItem', 10),
           )
        );
    }

    public function addScheduleItem(ProductionMenuCollectionEvent $event)
    {
        $menu = $event->getMenu();
        $group = $event->getGroup();

        // Create overview menu item.
        $schedule = $this->factory->createItem('menu_item.schedule', [
            'route' => 'bkstg_calendar_production',
            'routeParameters' => ['production_slug' => $group->getSlug()],
            'extras' => [
                'icon' => 'calendar',
                'translation_domain' => BkstgScheduleBundle::TRANSLATION_DOMAIN,
            ],
        ]);
        $menu->addChild($schedule);

        // $production = $this->factory->createItem('menu_item.production', [
        //     'route' => 'bkstg_calendar_production',
        //     'routeParameters' => ['production_slug' => $group->getSlug()],
        //     'extras' => ['translation_domain' => BkstgScheduleBundle::TRANSLATION_DOMAIN],
        // ]);
        // $schedule->addChild($production);
    }
}
