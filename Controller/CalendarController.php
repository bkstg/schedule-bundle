<?php

namespace Bkstg\ScheduleBundle\Controller;

use Bkstg\CoreBundle\Controller\Controller;
use Bkstg\CoreBundle\Entity\Production;
use Bkstg\ScheduleBundle\Entity\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CalendarController extends Controller
{
    /**
     * Show a calendar for the production.
     *
     * @param  string                        $production_slug The slug for the production.
     * @param  AuthorizationCheckerInterface $auth            The authorization checker service.
     * @param  Request                       $request         The current request.
     *
     * @throws NotFoundHttpException                          When the production does not exist.
     * @throws AccessDeniedException                          When the user is not an editor.
     *
     * @return Response                                       A response.
     */
    public function productionAction(
        string $production_slug,
        AuthorizationCheckerInterface $auth,
        Request $request
    ): Response {
        // Lookup the production by production_slug.
        $production_repo = $this->em->getRepository(Production::class);
        if (null === $production = $production_repo->findOneBy(['slug' => $production_slug])) {
            throw new NotFoundHttpException();
        }

        // Check permissions for this action.
        if (!$auth->isGranted('GROUP_ROLE_USER', $production)) {
            throw new AccessDeniedException();
        }

        return new Response($this->templating->render(
            '@BkstgSchedule/Calendar/production.html.twig',
            ['production' => $production]
        ));
    }

    /**
     * Search events for a production.
     *
     * @param  string                        $production_slug The slug for the production.
     * @param  AuthorizationCheckerInterface $auth            The authorization checker service.
     * @param  Request                       $request         The current request.
     *
     * @throws NotFoundHttpException                          When the production does not exist.
     * @throws AccessDeniedException                          When the user is not an editor.
     *
     * @return Response                                       A response.
     */
    public function searchProductionAction(
        string $production_slug,
        AuthorizationCheckerInterface $auth,
        Request $request
    ): Response {
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

        // Return a JSON response.
        return new JsonResponse($this->prepareResult($events, $production));
    }

    /**
     * Show a calendar for a user in a production.
     *
     * @param  string                        $production_slug The slug for the production.
     * @param  AuthorizationCheckerInterface $auth            The authorization checker service.
     * @param  Request                       $request         The current request.
     *
     * @throws NotFoundHttpException                          When the production does not exist.
     * @throws AccessDeniedException                          When the user is not an editor.
     *
     * @return Response                                       A response.
     */
    public function personalAction(
        string $production_slug,
        AuthorizationCheckerInterface $auth,
        Request $request
    ): Response {
        // Lookup the production by production_slug.
        $production_repo = $this->em->getRepository(Production::class);
        if (null === $production = $production_repo->findOneBy(['slug' => $production_slug])) {
            throw new NotFoundHttpException();
        }

        // Check permissions for this action.
        if (!$auth->isGranted('GROUP_ROLE_USER', $production)) {
            throw new AccessDeniedException();
        }

        return new Response($this->templating->render(
            '@BkstgSchedule/Calendar/personal.html.twig',
            ['production' => $production]
        ));
    }

    /**
     * Search events for a production.
     *
     * @param  string                        $production_slug The slug for the production.
     * @param  AuthorizationCheckerInterface $auth            The authorization checker service.
     * @param  TokenStorageInterface         $token           The user token.
     * @param  Request                       $request         The current request.
     *
     * @throws NotFoundHttpException                          When the production does not exist.
     * @throws AccessDeniedException                          When the user is not an editor.
     *
     * @return Response                                       A response.
     */
    public function searchPersonalAction(
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
        if (!$auth->isGranted('GROUP_ROLE_USER', $production)) {
            throw new AccessDeniedException();
        }

        // Lookup events.
        $event_repo = $this->em->getRepository(Event::class);
        $events = $event_repo->searchEventsByUser(
            $production,
            $token->getToken()->getUser(),
            new \DateTime('@' . ($request->query->get('from')/1000)),
            new \DateTime('@' . ($request->query->get('to')/1000))
        );

        // Return a JSON response.
        return new JsonResponse($this->prepareResult($events, $production));
    }

    /**
     * Helper function to prepare results for the calendar.
     *
     * @param  array      $events     The events to return.
     * @param  Production $production The production for these events.
     *
     * @return array                  The formatted events.
     */
    private function prepareResult(array $events, Production $production): array
    {
        // Create array of events for calendar.
        $result = [
            'success' => 1,
            'result' => [],
        ];

        // Index schedules so we don't duplicate them.
        $schedules = [];
        foreach ($events as $event) {
            if (null === $schedule = $event->getSchedule()) {
                // Add the event directly.
                $result['result'][] = [
                    'id' => 'event:' . $event->getId(),
                    'title' => $event->getName(),
                    'url' => $this->url_generator->generate(
                        'bkstg_event_show',
                        ['production_slug' => $production->getSlug(), 'id' => $event->getId()]
                    ),
                    'class' => 'event-' . $event->getType(),
                    'start' => $event->getStart()->format('U') * 1000,
                    'end' => $event->getEnd()->format('U') * 1000,
                ];
            } elseif (!isset($schedules[$schedule->getId()])) {
                // Add the schedule instead of the event.
                $schedules[$schedule->getId()] = true;
                $result['result'][] = [
                    'id' => 'schedule:' . $schedule->getId(),
                    'title' => $schedule->getTitle(),
                    'url' => $this->url_generator->generate(
                        'bkstg_schedule_show',
                        ['production_slug' => $production->getSlug(), 'id' => $schedule->getId()]
                    ),
                    'start' => $schedule->getStart()->format('U') * 1000,
                    'end' => $schedule->getEnd()->format('U') * 1000,
                ];
            }
        }
        return $result;
    }
}
