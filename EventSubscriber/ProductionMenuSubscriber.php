<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    /**
     * Create a new menu subscriber.
     *
     * @param FactoryInterface              $factory The menu factory service.
     * @param AuthorizationCheckerInterface $auth    The authorization checker service.
     */
    public function __construct(
        FactoryInterface $factory,
        AuthorizationCheckerInterface $auth
    ) {
        $this->factory = $factory;
        $this->auth = $auth;
    }

    /**
     * Return the subscribed events.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
           ProductionMenuCollectionEvent::NAME => [
               ['addScheduleItem', 10],
           ],
        ];
    }

    /**
     * Add the schedule menu item.
     *
     * @param ProductionMenuCollectionEvent $event The menu collection event.
     *
     * @return void
     */
    public function addScheduleItem(ProductionMenuCollectionEvent $event): void
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

        // If this user is an editor create the calendar and archive items.
        if ($this->auth->isGranted('GROUP_ROLE_EDITOR', $group)) {
            $calendar = $this->factory->createItem('menu_item.schedule_calendar', [
                'route' => 'bkstg_calendar_production',
                'routeParameters' => ['production_slug' => $group->getSlug()],
                'extras' => ['translation_domain' => BkstgScheduleBundle::TRANSLATION_DOMAIN],
            ]);
            $schedule->addChild($calendar);

            $archive = $this->factory->createItem('menu_item.schedule_archive', [
                'route' => 'bkstg_schedule_archive',
                'routeParameters' => ['production_slug' => $group->getSlug()],
                'extras' => ['translation_domain' => BkstgScheduleBundle::TRANSLATION_DOMAIN],
            ]);
            $schedule->addChild($archive);
        }
    }
}
