<?php

namespace Bkstg\ScheduleBundle\Controller;

use Bkstg\CoreBundle\Controller\Controller;
use Bkstg\CoreBundle\Entity\Production;
use Bkstg\ScheduleBundle\Entity\Schedule;
use Bkstg\ScheduleBundle\Form\ScheduleType;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
                $event->setColour($schedule->getColour());
                $event->setLocation($schedule->getLocation());
                $event->setActive($schedule->getActive());
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
            return new RedirectResponse($this->url_generator->generate(
                'bkstg_schedule_show',
                [
                    'id' => $schedule->getId(),
                    'production_slug' => $production->getSlug(),
                ]
            ));
        }

        // Render the form.
        return new Response($this->templating->render('@BkstgSchedule/Schedule/create.html.twig', [
            'form' => $form->createView(),
        ]));
    }

    public function readAction(
        $id,
        $production_slug,
        AuthorizationCheckerInterface $auth,
        PaginatorInterface $paginator,
        Request $request
    ) {
        // Get the schedule and production for this action.
        list($schedule, $production) = $this->lookupEntity(Schedule::class, $id, $production_slug);

        // Check permissions for this action.
        if (!$auth->isGranted('view', $schedule)) {
            throw new AccessDeniedException();
        }

        // Get and sort the events.
        $events = $schedule->getEvents()->toArray();
        usort($events, function ($a, $b) {
            return $a->getStart() > $b->getStart();
        });

        // Render the schedule.
        return new Response($this->templating->render('@BkstgSchedule/Schedule/read.html.twig', [
            'production' => $production,
            'schedule' => $schedule,
            'sorted_events' => $events,
        ]));
    }

    public function updateAction(
        string $production_slug,
        int $id,
        AuthorizationCheckerInterface $auth,
        TokenStorageInterface $token,
        Request $request
    ) {
        // Get the schedule and production for this action.
        list($schedule, $production) = $this->lookupEntity(Schedule::class, $id, $production_slug);

        // Check permissions for this action.
        if (!$auth->isGranted('edit', $schedule)) {
            throw new AccessDeniedException();
        }

        // Create an index of events and invitations for checking later.
        $events = new ArrayCollection();
        $invitations = [];
        foreach ($schedule->getEvents() as $event) {
            $events->add($event);
            $invitations[$event->getId()] = new ArrayCollection();
            foreach ($event->getInvitations() as $invitation) {
                $invitations[$event->getId()]->add($invitation);
            }
        }

        // Create and handle the form.
        $form = $this->form->create(ScheduleType::class, $schedule);
        $form->handleRequest($request);

        // If the form is submitted and valid persist the event.
        if ($form->isSubmitted() && $form->isValid()) {
            // Match events with schedule.
            foreach ($schedule->getEvents() as $event) {
                foreach ($schedule->getGroups() as $group) {
                    if (!$event->hasGroup($group)) {
                        $event->addGroup($group);
                    }
                }
                $event->setColour($schedule->getColour());
                $event->setLocation($schedule->getLocation());
                $event->setActive($schedule->getActive());
                $event->setAuthor($schedule->getAuthor());
            }

            // Remove unneeded events and invitations.
            foreach ($events as $event) {
                // First check for removed events.
                if (false === $schedule->getEvents()->contains($event)) {
                    $this->em->remove($event);
                } else {
                    // If event is still in schedule check invitations.
                    foreach($invitations[$event->getId()] as $invitation) {
                        if (false === $event->getInvitations()->contains($invitation)) {
                            $this->em->remove($invitation);
                        }
                    }
                }
            }

            $this->em->persist($schedule);
            $this->em->flush();

            // Set success message and redirect.
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('Schedule "%schedule%" edited.', [
                    '%schedule%' => $schedule->getTitle(),
                ])
            );
            return new RedirectResponse($this->url_generator->generate(
                'bkstg_schedule_show',
                ['id' => $schedule->getId(), 'production_slug' => $production->getSlug()]
            ));
        }

        // Render the form.
        return new Response($this->templating->render('@BkstgSchedule/Schedule/update.html.twig', [
            'schedule' => $schedule,
            'production' => $production,
            'form' => $form->createView(),
        ]));
    }

    public function deleteAction(
        string $production_slug,
        int $id,
        AuthorizationCheckerInterface $auth,
        Request $request
    ): Response {
        // Get the schedule and production for this action.
        list($schedule, $production) = $this->lookupEntity(Schedule::class, $id, $production_slug);

        // Check permissions for this action.
        if (!$auth->isGranted('edit', $schedule)) {
            throw new AccessDeniedException();
        }

        // Create a fake form to submit.
        $form = $this->form->createBuilder()
            ->add('id', HiddenType::class)
            ->getForm()
        ;

        // Handle the request.
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Remove the schedule and flush the entity manager.
            $this->em->remove($schedule);
            $this->em->flush();

            // Create flash message.
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('Deleted schedule "%title%".', [
                    '%title%' => $schedule->getTitle(),
                ])
            );

            // Redirect to schedule index.
            return new RedirectResponse($this->url_generator->generate(
                'bkstg_calendar_production',
                ['production_slug' => $production->getSlug()]
            ));
        }

        // Render the delete form.
        return new Response($this->templating->render('@BkstgSchedule/Schedule/delete.html.twig', [
            'schedule' => $schedule,
            'production' => $production,
            'form' => $form->createView(),
        ]));
    }
}
