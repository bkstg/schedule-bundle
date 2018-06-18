<?php

namespace Bkstg\ScheduleBundle\Controller;

use Bkstg\CoreBundle\Controller\Controller;
use Bkstg\ScheduleBundle\Entity\Invitation;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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

    public function respondAction(
        $id,
        $response,
        AuthorizationCheckerInterface $auth
    ) {
        $repo = $this->em->getRepository(Invitation::class);
        if (null === $invitation = $repo->findOneBy(['id' => $id])) {
            throw new NotFoundHttpException();
        }

        if (!$auth->isGranted('respond', $invitation)) {
            throw new AccessDeniedException();
        }

        switch ($response) {
            case 'accept':
                $invitation->setResponse(Invitation::RESPONSE_ACCEPT);
                break;
            case 'maybe':
                $invitation->setResponse(Invitation::RESPONSE_MAYBE);
                break;
            case 'decline':
                $invitation->setResponse(Invitation::RESPONSE_DECLINE);
                break;
        }

        $this->em->flush();
        return new JsonResponse();
    }
}
