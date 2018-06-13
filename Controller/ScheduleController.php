<?php

namespace Bkstg\ScheduleBundle\Controller;

use Bkstg\CoreBundle\Controller\Controller;
use Bkstg\CoreBundle\Entity\Production;
use Bkstg\ScheduleBundle\Entity\Schedule;
use Bkstg\ScheduleBundle\Form\ScheduleType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ScheduleController extends Controller
{
    /**
     * Create a new schedule, which is a collection of events.
     *
     * @param  string                        $production_slug The slug for the production.
     * @param  AuthorizationCheckerInterface $auth            The authorization checker service.
     * @param  TokenStorageInterface         $token           The user token service.
     * @param  Request                       $request         The current request.
     *
     * @throws NotFoundHttpException                          When the production does not exist.
     * @throws AccessDeniedException                          When the user is not an editor.
     *
     * @return Response                                       A response.
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

        // Check permissions for this action, must be an editor or better.
        if (!$auth->isGranted('GROUP_ROLE_EDITOR', $production)) {
            throw new AccessDeniedException();
        }

        // Create new schedule in this production.
        $schedule = new Schedule();
        $schedule->addGroup($production);
        $schedule->setAuthor($token->getToken()->getUser()->getUsername());

        // Create a form for this schedule and handle it.
        $form = $this->form->create(ScheduleType::class, $schedule);
        $form->handleRequest($request);

        // Form is submitted and valid.
        if ($form->isSubmitted() && $form->isValid()) {
            // Match events with schedule.
            foreach ($schedule->getEvents() as $event) {
                foreach ($schedule->getGroups() as $group) {
                    if (!$event->hasGroup($group)) {
                        $event->addGroup($group);
                    }
                }
                $event->setStatus(true);
                $event->setAuthor($schedule->getAuthor());
            }

            // Persist the schedule (will cascade persist).
            $this->em->persist($schedule);
            $this->em->flush();

            // Set success message and redirect.
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('Schedule "%schedule%" created.', [
                    '%schedule%' => $schedule->getTitle(),
                ])
            );
            return new RedirectResponse($this->url_generator->generate('bkstg_schedule_show', ['production_slug' => $production->getSlug()]));
        }

        // Render the form.
        return new Response($this->templating->render('@BkstgSchedule/Schedule/create.html.twig', [
            'form' => $form->createView(),
        ]));
    }

    public function showAction(
        $production_slug,
        AuthorizationCheckerInterface $auth,
        PaginatorInterface $paginator,
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

        return new Response($this->templating->render('@BkstgSchedule/Schedule/show.html.twig', [
            'production' => $production,
        ]));
    }

    public function showPersonalAction(
        $production_slug,
        AuthorizationCheckerInterface $auth,
        PaginatorInterface $paginator,
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

        return new Response($this->templating->render('@BkstgSchedule/Schedule/show.html.twig', [
            'production' => $production,
        ]));
    }
}
