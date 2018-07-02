<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgCoreBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\EventSubscriber;

use Bkstg\CoreBundle\Event\UserMenuCollectionEvent;
use Bkstg\ScheduleBundle\BkstgScheduleBundle;
use Bkstg\ScheduleBundle\Entity\Invitation;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserMenuSubscriber implements EventSubscriberInterface
{
    private $factory;
    private $em;
    private $token_storage;

    /**
     * Create a new user menu subscriber.
     *
     * @param FactoryInterface       $factory       The menu factory service.
     * @param EntityManagerInterface $em            The entity manager service.
     * @param TokenStorageInterface  $token_storage The token storage service.
     */
    public function __construct(
        FactoryInterface $factory,
        EntityManagerInterface $em,
        TokenStorageInterface $token_storage
    ) {
        $this->factory = $factory;
        $this->em = $em;
        $this->token_storage = $token_storage;
    }

    /**
     * Return the events this subscriber listens for.
     *
     * @return array The subscribed events.
     */
    public static function getSubscribedEvents(): array
    {
        return [
           UserMenuCollectionEvent::NAME => [
               ['addScheduleMenuItem', 0],
               ['addInvitationsMenuItem', -5],
           ],
        ];
    }

    /**
     * Add the schedule items to the user menu.
     *
     * @param UserMenuCollectionEvent $event The menu collection event.
     */
    public function addScheduleMenuItem(UserMenuCollectionEvent $event): void
    {
        // Get the menu from the event.
        $menu = $event->getMenu();

        // Create a separator first.
        $separator = $this->factory->createItem('schedule_separator', [
            'extras' => [
                'separator' => true,
                'translation_domain' => false,
            ],
        ]);
        $menu->addChild($separator);

        // Add link to user's calendar.
        $schedule = $this->factory->createItem('menu_item.my_schedule', [
            'route' => 'bkstg_calendar_personal',
            'extras' => [
                'icon' => 'calendar',
                'translation_domain' => BkstgScheduleBundle::TRANSLATION_DOMAIN,
            ],
        ]);
        $menu->addChild($schedule);
    }

    /**
     * Add invitations menu item.
     *
     * @param UserMenuCollectionEvent $event The menu collection event.
     */
    public function addInvitationsMenuItem(UserMenuCollectionEvent $event): void
    {
        // Get the menu from the event.
        $menu = $event->getMenu();

        // Lookup and count pending invitations.
        $repo = $this->em->getRepository(Invitation::class);
        $user = $this->token_storage->getToken()->getUser();
        $invitations = $repo->findPendingInvitations($user);

        // Create the pending invitations menu link.
        $invitations = $this->factory->createItem('menu_item.pending_invitations', [
            'route' => 'bkstg_invitation_index',
            'extras' => [
                'badge_count' => count($invitations),
                'translation_domain' => BkstgScheduleBundle::TRANSLATION_DOMAIN,
            ],
        ]);
        $menu->addChild($invitations);
    }
}
