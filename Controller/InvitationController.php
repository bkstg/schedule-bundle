<?php

namespace Bkstg\ScheduleBundle\Controller;

use Bkstg\CoreBundle\Controller\Controller;
use Bkstg\ScheduleBundle\Entity\Invitation;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class InvitationController extends Controller
{
    public function indexAction(
        Request $request,
        PaginatorInterface $paginator,
        TokenStorageInterface $token_storage
    ) {
        $repo = $this->em->getRepository(Invitation::class);
        $query = $repo->findPendingInvitationsQuery($token_storage->getToken()->getUser());
        $invitations = $paginator->paginate($query, $request->query->getInt('page', 1));
        return new Response($this->templating->render(
            '@BkstgSchedule/Invitation/index.html.twig',
            ['invitations' => $invitations]
        ));
    }
}
