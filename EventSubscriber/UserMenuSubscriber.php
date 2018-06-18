<?php

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

    public function __construct(
        FactoryInterface $factory,
        EntityManagerInterface $em,
        TokenStorageInterface $token_storage
    ) {
        $this->factory = $factory;
        $this->em = $em;
        $this->token_storage = $token_storage;
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
           UserMenuCollectionEvent::NAME => [
               ['addScheduleMenuItem', 0],
               ['addInvitationsMenuItem', -5],
           ],
        ];
    }

    public function addScheduleMenuItem(UserMenuCollectionEvent $event)
    {
        $menu = $event->getMenu();

        $separator = $this->factory->createItem('schedule_separator', [
            'extras' => [
                'separator' => true,
                'translation_domain' => false,
            ],
        ]);
        $menu->addChild($separator);

        $schedule = $this->factory->createItem('menu_item.my_schedule', [
            'route' => 'bkstg_calendar_personal',
            'extras' => [
                'icon' => 'calendar',
                'translation_domain' => BkstgScheduleBundle::TRANSLATION_DOMAIN,
            ],
        ]);
        $menu->addChild($schedule);
    }

    public function addInvitationsMenuItem(UserMenuCollectionEvent $event)
    {
        $menu = $event->getMenu();

        $repo = $this->em->getRepository(Invitation::class);
        $user = $this->token_storage->getToken()->getUser();
        $invitations = $repo->findPendingInvitations($user);

        $invitations = $this->factory->createItem('menu_item.pending_invitations', [
            'route' => 'bkstg_invitation_index',
            'extras' => [
                'badge_count' => count($invitations),
                'translation_domain' => BkstgScheduleBundle::TRANSLATION_DOMAIN,
            ]
        ]);
        $menu->addChild($invitations);
    }
}
