<?php

namespace Bkstg\ScheduleBundle\Controller;

use Bkstg\CoreBundle\Controller\Controller;
use Bkstg\CoreBundle\Entity\Production;
use Bkstg\ScheduleBundle\Entity\Event;
use Bkstg\ScheduleBundle\Form\EventType;
use Knp\Component\Pager\PaginatorInterface;
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
    public function showAction(
        $production_slug,
        $id,
        AuthorizationCheckerInterface $auth
    ) {
        // Lookup the production by production_slug.
        $production_repo = $this->em->getRepository(Production::class);
        if (null === $production = $production_repo->findOneBy(['slug' => $production_slug])) {
            throw new NotFoundHttpException();
        }

        // Lookup the event by id.
        $event_repo = $this->em->getRepository(Event::class);
        if (null === $event = $event_repo->findOneBy(['id' => $id])) {
            throw new NotFoundHttpException();
        }

        // Check permissions for this action.
        if (!$auth->isGranted('view', $event)) {
            throw new AccessDeniedException();
        }

        return new Response($this->templating->render('@BkstgSchedule/Event/show.html.twig', [
            'production' => $production,
            'event' => $event,
        ]));
    }

    public function createAction(
        $production_slug,
        AuthorizationCheckerInterface $auth,
        TokenStorageInterface $token,
        Request $request
    ) {
        // Lookup the production by production_slug.
        $production_repo = $this->em->getRepository(Production::class);
        if (null === $production = $production_repo->findOneBy(['slug' => $production_slug])) {
            throw new NotFoundHttpException();
        }

        // Check permissions for this action.
        if (!$auth->isGranted('GROUP_ROLE_EDITOR', $production)) {
            throw new AccessDeniedException();
        }

        $event = new Event();
        $event->addGroup($production);
        $event->setAuthor($token->getToken()->getUser()->getUsername());
        $event->setStart(new \DateTime());
        $event->setEnd(new \DateTime('+1hour'));

        $form = $this->form->create(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($event);
            $this->em->flush();

            // Set success message and redirect.
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('Event "%event%" created.', [
                    '%event%' => $event->getName(),
                ])
            );
            return new RedirectResponse($this->url_generator->generate('bkstg_schedule_show', ['production_slug' => $production->getSlug()]));
        }

        // Render the form.
        return new Response($this->templating->render('@BkstgSchedule/Event/create.html.twig', [
            'form' => $form->createView(),
        ]));
    }

    public function indexAction(
        $production_slug,
        AuthorizationCheckerInterface $auth,
        Request $request
    ) {
        // Lookup the production by production_slug.
        $production_repo = $this->em->getRepository(Production::class);
        if (null === $production = $production_repo->findOneBy(['slug' => $production_slug])) {
            throw new NotFoundHttpException();
        }

        // Check permissions for this action.
        if (!$auth->isGranted('GROUP_ROLE_USER', $production)) {
            throw new AccessDeniedException();
        }

        // Lookup events.
        $event_repo = $this->em->getRepository(Event::class);
        $events = $event_repo->searchEvents(
            $production,
            new \DateTime('@' . ($request->query->get('from')/1000)),
            new \DateTime('@' . ($request->query->get('to')/1000))
        );

        // Create array of events for calendar.
        $result = [
            'success' => 1,
            'result' => [],
        ];
        foreach ($events as $event) {
            $result['result'][] = [
                'id' => $event->getId(),
                'title' => $event->getName(),
                'url' => $this->url_generator->generate('bkstg_event_show', ['production_slug' => $production->getSlug(), 'id' => $event->getId()]),
                'class' => 'event-' . $event->getType(),
                'start' => $event->getStart()->format('U') * 1000,
                'end' => $event->getEnd()->format('U') * 1000,
            ];
        }

        return new JsonResponse($result);
    }
}
