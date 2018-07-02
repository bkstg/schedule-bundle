<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgCoreBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    /**
     * Show a list of pending invitations for the current user.
     *
     * @param Request               $request       The incoming request.
     * @param PaginatorInterface    $paginator     The paginator service.
     * @param TokenStorageInterface $token_storage The token storage service.
     *
     * @return Response
     */
    public function indexAction(
        Request $request,
        PaginatorInterface $paginator,
        TokenStorageInterface $token_storage
    ): Response {
        // Get the invitations repo and a list of pending invites for this user.
        $repo = $this->em->getRepository(Invitation::class);
        $query = $repo->findPendingInvitationsQuery($token_storage->getToken()->getUser());

        // Paginate and render the invitations.
        $invitations = $paginator->paginate($query, $request->query->getInt('page', 1));

        return new Response($this->templating->render(
            '@BkstgSchedule/Invitation/index.html.twig',
            ['invitations' => $invitations]
        ));
    }

    /**
     * Show a list of old/responded invitations for a user.
     *
     * @param Request               $request       The incoming request.
     * @param PaginatorInterface    $paginator     The paginator service.
     * @param TokenStorageInterface $token_storage The token storage service.
     *
     * @return Response
     */
    public function archiveAction(
        Request $request,
        PaginatorInterface $paginator,
        TokenStorageInterface $token_storage
    ): Response {
        // Get the invitations repo and a list of other invites for this user.
        $repo = $this->em->getRepository(Invitation::class);
        $query = $repo->findOtherInvitationsQuery($token_storage->getToken()->getUser());

        // Paginate and render the invitations.
        $invitations = $paginator->paginate($query, $request->query->getInt('page', 1));

        return new Response($this->templating->render(
            '@BkstgSchedule/Invitation/archive.html.twig',
            ['invitations' => $invitations]
        ));
    }

    /**
     * Respond to an individual invitation.
     *
     * @param int                           $id       The id of the invitation.
     * @param string                        $response The response to the invitation.
     * @param AuthorizationCheckerInterface $auth     The authorization checker service.
     *
     * @throws NotFoundHttpException When the invitation does not exist.
     * @throws AccessDeniedException When the user has no access to respond.
     *
     * @return Response
     */
    public function respondAction(
        int $id,
        string $response,
        AuthorizationCheckerInterface $auth
    ): Response {
        // Get the repo and lookup the invitation.
        $repo = $this->em->getRepository(Invitation::class);
        if (null === $invitation = $repo->findOneBy(['id' => $id])) {
            throw new NotFoundHttpException();
        }

        // Check that this user is allowed to respond.
        if (!$auth->isGranted('respond', $invitation)) {
            throw new AccessDeniedException();
        }

        // Record response.
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

        // Return empty json response (code 200).
        return new JsonResponse();
    }
}
