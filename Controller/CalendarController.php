<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @param string                        $production_slug The slug for the production.
     * @param AuthorizationCheckerInterface $auth            The authorization checker service.
     * @param Request                       $request         The current request.
     *
     * @throws NotFoundHttpException When the production does not exist.
     * @throws AccessDeniedException When the user is not an editor.
     *
     * @return Response A response.
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
     * @param string                        $production_slug The slug for the production.
     * @param AuthorizationCheckerInterface $auth            The authorization checker service.
     * @param Request                       $request         The current request.
     *
     * @throws NotFoundHttpException When the production does not exist.
     * @throws AccessDeniedException When the user is not an editor.
     *
     * @return Response A response.
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
            new \DateTime('@' . ($request->query->get('from') / 1000)),
            new \DateTime('@' . ($request->query->get('to') / 1000))
        );

        // Return a JSON response.
        return new JsonResponse($this->prepareResult($events, $production));
    }

    /**
     * Show a calendar for a user.
     *
     * @param AuthorizationCheckerInterface $auth    The authorization checker service.
     * @param Request                       $request The current request.
     *
     * @throws NotFoundHttpException When the production does not exist.
     * @throws AccessDeniedException When the user is not an editor.
     *
     * @return Response A response.
     */
    public function personalAction(
        AuthorizationCheckerInterface $auth,
        Request $request
    ): Response {
        return new Response($this->templating->render(
            '@BkstgSchedule/Calendar/personal.html.twig'
        ));
    }

    /**
     * Search events for a production.
     *
     * @param AuthorizationCheckerInterface $auth    The authorization checker service.
     * @param TokenStorageInterface         $token   The user token.
     * @param Request                       $request The current request.
     *
     * @throws NotFoundHttpException When the production does not exist.
     * @throws AccessDeniedException When the user is not an editor.
     *
     * @return Response A response.
     */
    public function searchPersonalAction(
        AuthorizationCheckerInterface $auth,
        TokenStorageInterface $token,
        Request $request
    ): Response {
        // Lookup events.
        $event_repo = $this->em->getRepository(Event::class);
        $events = $event_repo->searchEventsByUser(
            $token->getToken()->getUser(),
            new \DateTime('@' . ($request->query->get('from') / 1000)),
            new \DateTime('@' . ($request->query->get('to') / 1000))
        );

        // Return a JSON response.
        return new JsonResponse($this->prepareResult($events, null));
    }

    /**
     * Helper function to prepare results for the calendar.
     *
     * @param array      $events     The events to return.
     * @param Production $production The production for these events.
     *
     * @return array The formatted events.
     */
    private function prepareResult(array $events, Production $production = null): array
    {
        // Create array of events for calendar.
        $result = [
            'success' => 1,
            'result' => [],
        ];

        // Index schedules so we don't duplicate them.
        $schedules = [];
        foreach ($events as $event) {
            $event_production = (null !== $production) ? $production : $event->getGroups()[0];
            if (null === $schedule = $event->getSchedule()) {
                // Add the event directly.
                $result['result'][] = [
                    'icon' => 'calendar',
                    'id' => 'event:' . $event->getId(),
                    'title' => ((null === $production) ? $event_production->getName() . ': ' : '') . $event->getName(),
                    'url' => $this->url_generator->generate(
                        'bkstg_event_read',
                        ['production_slug' => $event_production->getSlug(), 'id' => $event->getId()]
                    ),
                    'class' => 'event-' . $event->getColour(),
                    'start' => $event->getStart()->format('U') * 1000,
                    'end' => $event->getEnd()->format('U') * 1000,
                ];
            } elseif (!isset($schedules[$schedule->getId()])) {
                // Add the schedule instead of the event.
                $schedules[$schedule->getId()] = true;
                $result['result'][] = [
                    'icon' => 'list',
                    'id' => 'schedule:' . $schedule->getId(),
                    'title' => (
                        (null === $production) ? $event_production->getName() . ': ' : ''
                    ) . $schedule->getName(),
                    'url' => $this->url_generator->generate(
                        'bkstg_schedule_read',
                        ['production_slug' => $event_production->getSlug(), 'id' => $schedule->getId()]
                    ),
                    'class' => 'event-' . $schedule->getColour(),
                    'start' => $schedule->getStart()->format('U') * 1000,
                    'end' => $schedule->getEnd()->format('U') * 1000,
                ];
            }
        }

        return $result;
    }
}
