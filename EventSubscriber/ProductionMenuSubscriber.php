<?php

namespace Bkstg\ScheduleBundle\EventSubscriber;

use Bkstg\CoreBundle\Event\ProductionMenuCollectionEvent;
use Bkstg\CoreBundle\Menu\Item\IconMenuItem;
use Knp\Menu\FactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductionMenuSubscriber implements EventSubscriberInterface
{

    private $factory;
    private $url_generator;
    private $auth;

    public function __construct(
        FactoryInterface $factory,
        UrlGeneratorInterface $url_generator,
        AuthorizationCheckerInterface $auth
    ) {
        $this->factory = $factory;
        $this->url_generator = $url_generator;
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
        $schedule = $this->factory->createItem('Schedule', [
            'uri' => $this->url_generator->generate(
                'bkstg_schedule_show',
                ['production_slug' => $group->getSlug()]
            ),
            'extras' => ['icon' => 'calendar'],
        ]);
        $production = $this->factory->createItem('Production', [
            'uri' => $this->url_generator->generate(
                'bkstg_schedule_show',
                ['production_slug' => $group->getSlug()]
            ),
        ]);
        $my_schedule = $this->factory->createItem('My Schedule', [
            'uri' => $this->url_generator->generate(
                'bkstg_schedule_personal',
                ['production_slug' => $group->getSlug()]
            ),
        ]);
        $schedule->addChild($production);
        $schedule->addChild($my_schedule);
        $menu->addChild($schedule);
    }
}
