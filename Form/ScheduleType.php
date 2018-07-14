<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\Form;

use Bkstg\ScheduleBundle\BkstgScheduleBundle;
use Bkstg\ScheduleBundle\Entity\Schedule;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduleType extends AbstractType
{
    /**
     * {@inheritdoc}
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options for this form.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $schedule = $options['data'];
        $builder
            ->add('name', null, [
                'label' => 'schedule.form.name',
            ])
            ->add('location', null, [
                'label' => 'schedule.form.location',
            ])
            ->add('description', CKEditorType::class, [
                'label' => 'schedule.form.description',
                'config' => ['toolbar' => 'basic'],
                'required' => false, ])
            ->add('colour', ChoiceType::class, [
                'label' => 'schedule.form.colour',
                'required' => false,
                'choices' => [
                    'schedule.form.colour_choices.red' => 'important',
                    'schedule.form.colour_choices.green' => 'success',
                    'schedule.form.colour_choices.yellow' => 'warning',
                    'schedule.form.colour_choices.blue' => 'info',
                    'schedule.form.colour_choices.dark' => 'inverse',
                    'schedule.form.colour_choices.purple' => 'special',
                ],
            ])
            ->add('active', ChoiceType::class, [
                // Show "unpublished" instead of active.
                'choice_loader' => new CallbackChoiceLoader(function () use ($schedule) {
                    yield 'schedule.form.status_choices.active' => true;
                    if (!$schedule->isPublished()) {
                        yield 'schedule.form.status_choices.unpublished' => false;
                    } else {
                        yield 'schedule.form.status_choices.archived' => false;
                    }
                }),
                'label' => 'schedule.form.status',
            ])
            ->add('events', CollectionType::class, [
                'label' => 'schedule.form.events',
                'entry_type' => ScheduleEventType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    /**
     * Set default options.
     *
     * @param OptionsResolver $resolver The options resolver.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Schedule::class,
            'translation_domain' => BkstgScheduleBundle::TRANSLATION_DOMAIN,
        ]);
    }
}
