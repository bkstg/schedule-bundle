<?php

namespace Bkstg\ScheduleBundle\Controller;

use Bkstg\CoreBundle\Controller\Controller;
use Bkstg\CoreBundle\Entity\Production;
use Bkstg\ScheduleBundle\BkstgScheduleBundle;
use Bkstg\ScheduleBundle\Entity\Event;
use Bkstg\ScheduleBundle\Form\EventType;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EventController extends Controller
{
    /**
     * Create a new standalone event.
     *
     * @param  string                        $production_slug The slug for the production.
     * @param  AuthorizationCheckerInterface $auth            The authorization checker service.
     * @param  TokenStorageInterface         $token           The token storage service.
     * @param  Request                       $request         The request.
     * @throws NotFoundHttpException                          When the production does not exist.
     * @throws AccessDeniedException                          When the user is not an editor.
     * @return Response                                       The response.
     */
    public function createAction(
        string $production_slug,
        AuthorizationCheckerInterface $auth,
        TokenStorageInterface $token,
        Request $request
    ): Response {
        // Lookup the production by production_slug.
        $production_repo = $this->em->getRepository(Production::class);
        if (null === $production = $production_repo->findOneBy(['slug' => $production_slug])) {
            throw new NotFoundHttpException();
        }

        // Check permissions for this action.
        if (!$auth->isGranted('GROUP_ROLE_EDITOR', $production)) {
            throw new AccessDeniedException();
        }

        // Create a new event with author and production.
        $event = new Event();
        $event->addGroup($production);
        $event->setAuthor($token->getToken()->getUser()->getUsername());
        $event->setActive(true);

        // Set start and end times using closest 1 hour intervals.
        $start = new \DateTime();
        $start->modify('+1 hour');
        $start->modify('-' . $start->format('i') . ' minutes');
        $event->setStart($start);

        $end = new \DateTime('+1 hour');
        $end->modify('+1 hour');
        $end->modify('-' . $end->format('i') . ' minutes');
        $event->setEnd($end);

        // Create and handle the form.
        $form = $this->form->create(EventType::class, $event);
        $form->handleRequest($request);

        // If the form is submitted and valid persist the event.
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($event);
            $this->em->flush();

            // Set success message and redirect.
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('event.created', [
                    '%event%' => $event->getName(),
                ], BkstgScheduleBundle::TRANSLATION_DOMAIN)
            );
            return new RedirectResponse($this->url_generator->generate(
                'bkstg_event_read',
                ['id' => $event->getId(), 'production_slug' => $production->getSlug()]
            ));
        }

        // Render the form.
        return new Response($this->templating->render('@BkstgSchedule/Event/create.html.twig', [
            'form' => $form->createView(),
        ]));
    }

    /**
     * Show a single event.
     *
     * @param  string                        $production_slug The slug for the production.
     * @param  integer                       $id              The event id.
     * @param  AuthorizationCheckerInterface $auth            The authorization checker service.
     * @throws AccessDeniedException                          When the user is not in the group.
     * @return Response                                       The response.
     */
    public function readAction(
        string $production_slug,
        int $id,
        AuthorizationCheckerInterface $auth
    ): Response {
        // Get the event and production for this action.
        list($event, $production) = $this->lookupEntity(Event::class, $id, $production_slug);

        // If this event is handled by a schedule redirect there.
        if (null !== $redirect = $this->checkSchedule($event, $production)) {
            return $redirect;
        }

        // Check permissions for this action.
        if (!$auth->isGranted('view', $event)) {
            throw new AccessDeniedException();
        }

        // Render the event.
        return new Response($this->templating->render('@BkstgSchedule/Event/read.html.twig', [
            'production' => $production,
            'event' => $event,
        ]));
    }

    /**
     * Update a standalone event.
     *
     * @param  string                        $production_slug The slug for the production.
     * @param  integer                       $id              The event id.
     * @param  AuthorizationCheckerInterface $auth            The authorization checker service.
     * @param  TokenStorageInterface         $token           The token storage service.
     * @param  Request                       $request         The request.
     * @throws AccessDeniedException                          When the user is not an editor.
     * @return Response                                       The response.
     */
    public function updateAction(
        string $production_slug,
        int $id,
        AuthorizationCheckerInterface $auth,
        TokenStorageInterface $token,
        Request $request
    ): Response {
        // Get the event and production for this action.
        list($event, $production) = $this->lookupEntity(Event::class, $id, $production_slug);

        // If this event is handled by a schedule redirect there.
        if (null !== $redirect = $this->checkSchedule($event, $production)) {
            return $redirect;
        }

        // Check permissions for this action.
        if (!$auth->isGranted('edit', $event)) {
            throw new AccessDeniedException();
        }

        // Create an index of invitations for checking later.
        $invitations = new ArrayCollection();
        foreach ($event->getInvitations() as $invitation) {
            $invitations->add($invitation);
        }

        // Create and handle the form.
        $form = $this->form->create(EventType::class, $event);
        $form->handleRequest($request);

        // If the form is submitted and valid persist the event.
        if ($form->isSubmitted() && $form->isValid()) {
            // Remove unneeded invitations.
            foreach ($invitations as $invitation) {
                if (false === $event->getInvitations()->contains($invitation)) {
                    $this->em->remove($invitation);
                }
            }

            $this->em->persist($event);
            $this->em->flush();

            // Set success message and redirect.
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('event.updated', [
                    '%event%' => $event->getName(),
                ], BkstgScheduleBundle::TRANSLATION_DOMAIN)
            );
            return new RedirectResponse($this->url_generator->generate(
                'bkstg_event_read',
                ['id' => $event->getId(), 'production_slug' => $production->getSlug()]
            ));
        }

        // Render the form.
        return new Response($this->templating->render('@BkstgSchedule/Event/update.html.twig', [
            'event' => $event,
            'production' => $production,
            'form' => $form->createView(),
        ]));
    }

    /**
     * Delete a single event.
     *
     * @param  string                        $production_slug The slug for the production.
     * @param  integer                       $id              The event id.
     * @param  AuthorizationCheckerInterface $auth            The authorization checker service.
     * @param  Request                       $request         The request.
     * @throws AccessDeniedException                          When the user is not an editor.
     * @return Response                                       The response.
     */
    public function deleteAction(
        string $production_slug,
        int $id,
        AuthorizationCheckerInterface $auth,
        Request $request
    ): Response {
        // Get the event and production for this action.
        list($event, $production) = $this->lookupEntity(Event::class, $id, $production_slug);

        // If this event is handled by a schedule redirect there.
        if (null !== $redirect = $this->checkSchedule($event, $production)) {
            return $redirect;
        }

        // Check permissions for this action.
        if (!$auth->isGranted('edit', $event)) {
            throw new AccessDeniedException();
        }

        // Create and handle a fake form to submit.
        $form = $this->form->createBuilder()->getForm();
        $form->handleRequest($request);

        // If form is submitted and valid.
        if ($form->isSubmitted() && $form->isValid()) {
            // Remove the event and flush the entity manager.
            $this->em->remove($event);
            $this->em->flush();

            // Create flash message.
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('event.deleted', [
                    '%event%' => $event->getName(),
                ], BkstgScheduleBundle::TRANSLATION_DOMAIN)
            );

            // Redirect to the production calendar.
            return new RedirectResponse($this->url_generator->generate(
                'bkstg_calendar_production',
                ['production_slug' => $production->getSlug()]
            ));
        }

        // Render the delete form.
        return new Response($this->templating->render('@BkstgSchedule/Event/delete.html.twig', [
            'event' => $event,
            'production' => $production,
            'form' => $form->createView(),
        ]));
    }

    /**
     * Show a list of archived events.
     *
     * @param  string                        $production_slug The production to look in.
     * @param  PaginatorInterface            $paginator       The paginator service.
     * @param  AuthorizationCheckerInterface $auth            The authorization checker service.
     * @param  Request                       $request         The incoming request.
     * @throws NotFoundHttpException                          When the production does not exist.
     * @throws AccessDeniedException                          When the user is not an editor.
     * @return Response
     */
    public function archiveAction(
        string $production_slug,
        PaginatorInterface $paginator,
        AuthorizationCheckerInterface $auth,
        Request $request
    ): Response {
        // Lookup the production by production_slug.
        $production_repo = $this->em->getRepository(Production::class);
        if (null === $production = $production_repo->findOneBy(['slug' => $production_slug])) {
            throw new NotFoundHttpException();
        }

        // Check permissions for this action.
        if (!$auth->isGranted('GROUP_ROLE_EDITOR', $production)) {
            throw new AccessDeniedException();
        }

        // Get a list of archived events.
        $event_repo = $this->em->getRepository(Event::class);
        $query = $event_repo->findArchivedEventsQuery($production);

        // Paginate and render the results.
        $events = $paginator->paginate($query, $request->query->getInt('page', 1));
        return new Response($this->templating->render('@BkstgSchedule/Event/archive.html.twig', [
            'events' => $events,
            'production' => $production,
        ]));
    }

    /**
     * Check if an event has a parent schedule.
     *
     * @param  Event      $event      The event to check.
     * @param  Production $production The production this event is in.
     * @return RedirectResponse
     */
    private function checkSchedule(Event $event, Production $production): ?RedirectResponse
    {
        // If this event is handled by a schedule generate a redirect there.
        if (null !== $schedule = $event->getSchedule()) {
            return new RedirectResponse($this->url_generator->generate(
                'bkstg_schedule_read',
                [
                    'production_slug' => $production->getSlug(),
                    'id' => $schedule->getId()
                ]
            ));
        }

        return null;
    }
}
