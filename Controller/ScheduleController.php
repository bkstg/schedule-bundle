<?php

namespace Bkstg\ScheduleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation as Http;
use Doctrine\ORM\EntityManager;
use Bkstg\ScheduleBundle\Entity\Schedule;
use Bkstg\ScheduleBundle\Entity\ScheduleItem;
use Bkstg\ScheduleBundle\Form\ScheduleType;
use Bkstg\CoreBundle\Manager\MessageManager;

/**
 * @Route\Route("/schedule")
 */
class ScheduleController extends Controller
{
    /**
     * @Route\Route("/", name="bkstg_schedule_home")
     *
     * Simply provides a redirect to the next available schedule after the
     * current date.
     */
    public function indexAction()
    {
        // get entity manager and next available date
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $result = $qb
            // check for schedule items
            ->select('i')
            ->from('BkstgScheduleBundle:ScheduleItem', 'i')

            // join schedules
            ->join('i.schedule', 's')
            ->where('s.status = :status')
            ->andWhere('i.datetimeStart > :date')
            ->orderBy('i.datetimeStart', 'ASC')
            ->setParameter(':date', new \DateTime())
            ->setParameter(':status', Schedule::STATUS_ACTIVE)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (count($result) > 0) {
            $datestart = $result[0]->getDatetimeStart();
        } else {
            $datestart = new \DateTime();
        }

        $dateend = new \DateTime($datestart->format('Y-m-d'));
        $dateend->modify('+1 week');

        return $this->redirectToRoute('bkstg_schedule_show', array(
            'datestart' => $datestart->format('Y-m-d'),
            'dateend' => $dateend->format('Y-m-d'),
        ));

    }

    /**
     * @Route\Route("/add", name="bkstg_schedule_add_schedule")
     *
     * Route to handle add schedule form.
     *
     * A schedule is a collection of schedule items, most often these are on the
     * same day, but that is not enforced.  Schedule items are always added as
     * unpublished and can be published from the schedule unpublished screen.
     */
    public function addAction(Http\Request $request)
    {
        // editors only
        $this->denyAccessUnlessGranted('ROLE_EDITOR', null, 'Unable to access this page!');

        // get current user from security context
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // get entity manager and generate form handler
        $em = $this->getDoctrine()->getManager();
        $schedule = new Schedule();

        $form = $this->createForm(new ScheduleType(), $schedule);

        // handle this form enforce this user
        $form->handleRequest($request);
        $schedule->setUser($user);
        $schedule->setStatus(Schedule::STATUS_DRAFT);

        if ($form->isValid()) {
            $em->persist($schedule);
            $em->flush();

            // success message
            $this->addFlash(
                'success',
                'Schedule "' . $schedule->getName() . '" added!'
            );

            // redirect
            return $this->redirectToRoute('bkstg_schedule_home');
        }

        // get message manager
        $message_manager = $this->get('message.manager');

        return $this->render('BkstgScheduleBundle:Form:schedule_form.html.twig', array(
            'form' => $form->createView(),
            'message_manager' => $message_manager,
        ));
    }

    /**
     * @Route\Route("/publish/{schedule}", name="bkstg_schedule_publish_schedule")
     * @Route\ParamConverter("schedule", class="BkstgScheduleBundle:Schedule")
     * @Route\Security("has_role('ROLE_EDITOR')")
     */
    public function publishAction(Schedule $schedule, Http\Request $request)
    {
        // get entity manager and generate form handler
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $schedule->setStatus(Schedule::STATUS_ACTIVE);
        $em->persist($schedule);
        $em->flush();

        // success message
        $this->addFlash(
            'success',
            'Schedule "' . $schedule->getName() . '" pusblished!'
        );

        // create new site message
        $mm = $this->get('message.manager');
        $mm->createMessage("$user published a new schedule", 'time', 'bkstg_schedule_home', null, 'BkstgScheduleBundle:Schedule', $schedule);

        // redirect
        return $this->redirectToRoute('bkstg_schedule_home');
    }

    /**
     * @Route\Route("/edit/{schedule}", name="bkstg_schedule_edit_schedule")
     * @Route\ParamConverter("schedule", class="BkstgScheduleBundle:Schedule")
     * @Route\Security("has_role('ROLE_EDITOR')")
     */
    public function editAction(Schedule $schedule, Http\Request $request)
    {
        // get current user from security context
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // get entity manager and generate form handler
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new ScheduleType(), $schedule);

        // original schedule items
        $original_schedule_items = new ArrayCollection();

        // Create an ArrayCollection of the current ScheduleItem objects in the database
        foreach ($schedule->getScheduleItems() as $schedule_item) {
            $original_schedule_items->add($schedule_item);
        }

        // handle this form enforce this user and reset to draft status
        $form->handleRequest($request);
        $schedule->setUser($user);
        $schedule->setStatus(Schedule::STATUS_DRAFT);

        if ($form->isValid()) {
            // remove the relationship between the ScheduleItem and the Schedule
            foreach ($original_schedule_items as $schedule_item) {
                if ($schedule->getScheduleItems()->contains($schedule_item) === false) {
                    // delete the schedule item
                    $em->remove($schedule_item);
                }
            }
            $em->persist($schedule);
            $em->flush();

            // success message
            $this->addFlash(
                'success',
                'Schedule "' . $schedule->getName() . '" edited!'
            );

            // redirect
            return $this->redirectToRoute('bkstg_schedule_home');
        }

        // get message manager
        $message_manager = $this->get('message.manager');

        return $this->render('BkstgScheduleBundle:Form:schedule_form.html.twig', array(
            'form' => $form->createView(),
            'title' => 'Edit Schedule',
            'submit_value' => 'Save changes',
            'message_manager' => $message_manager,
        ));
    }

    /**
     * @Route\Route("/delete/{schedule}", name="bkstg_schedule_delete_schedule")
     * @Route\ParamConverter("schedule", class="BkstgScheduleBundle:Schedule")
     * @Route\Security("has_role('ROLE_EDITOR')")
     */
    public function deleteAction(Schedule $schedule, Http\Request $request)
    {
        // get entity manager and remove sschedule and items
        $em = $this->getDoctrine()->getManager();
        foreach ($schedule->getScheduleItems() as $schedule_item) {
            $em->remove($schedule_item);
        }

        $em->remove($schedule);
        $em->flush();

        // success message
        $this->addFlash('warning', 'Schedule deleted!');

        // redirect
        return $this->redirectToRoute('bkstg_schedule_home');
    }

    /**
     * @Route\Route("/{datestart}/{dateend}", name="bkstg_schedule_show")
     * @Route\ParamConverter("datestart", options={"format": "Y-m-d"})
     * @Route\ParamConverter("dateend", options={"format": "Y-m-d"})
     */
    public function showAction(\DateTime $datestart, \DateTime $dateend, Http\Request $request)
    {
        // create filter form
        $form = $this->createFormBuilder()
            ->add('datestart', 'date', array('label' => 'Between', 'widget' => 'single_text', 'data' => $datestart))
            ->add('dateend', 'date', array('label' => 'And', 'widget' => 'single_text', 'data' => $dateend))
            ->getForm();

        // handle form request and redirect to new date route
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute('bkstg_schedule_show', array(
                'datestart' => $data['datestart']->format('Y-m-d'),
                'dateend' => $data['dateend']->format('Y-m-d'),
            ));
        }

        // put end date at end of day
        $dateend->setTime(23, 59, 59);

        // get entity manager and schedules for these dates
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb
            ->select('s')
            ->from('BkstgScheduleBundle:Schedule', 's')
            ->join('s.scheduleItems', 'i')
            ->where(
                $qb->expr()->between(
                    'i.datetimeStart',
                    ':start',
                    ':end'
                )
            )
            ->andWhere('s.status = :status')
            ->addOrderBy('i.datetimeStart', 'ASC')
            ->distinct()
            ->setParameter('start', $datestart)
            ->setParameter('end', $dateend)
            ->setParameter('status', Schedule::STATUS_ACTIVE);

        $schedules = $qb->getQuery()->getResult();

        // get message manager
        $message_manager = $this->get('message.manager');

        return $this->render('BkstgScheduleBundle::schedule.html.twig', array(
            'schedules' => $schedules,
            'datestart' => $datestart,
            'dateend' => $dateend,
            'filter_form' => $form->createView(),
            'message_manager' => $message_manager,
        ));
    }

    /**
     * @Route\Route("/un-published", name="bkstg_schedule_unpublished")
     * @Route\Security("has_role('ROLE_EDITOR')")
     */
    public function unpublishedAction()
    {
        // get entity manager and schedules for these dates
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('s')
            ->from('BkstgScheduleBundle:Schedule', 's')
            ->join('s.scheduleItems', 'i')
            ->andWhere('s.status = :status')
            ->addOrderBy('i.datetimeStart', 'ASC')
            ->distinct()
            ->setParameter('status', Schedule::STATUS_DRAFT);
        $schedules = $qb->getQuery()->getResult();

        // get message manager
        $message_manager = $this->get('message.manager');

        return $this->render('BkstgScheduleBundle::schedule.html.twig', array(
            'schedules' => $schedules,
            'message_manager' => $message_manager,
        ));
    }
}
