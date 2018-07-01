<?php

namespace Bkstg\ScheduleBundle\Form;

use Bkstg\ScheduleBundle\BkstgScheduleBundle;
use Bkstg\ScheduleBundle\Entity\Schedule;
use Bkstg\ScheduleBundle\Form\ScheduleEventType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduleType extends AbstractType
{
    /**
     * {@inheritdoc}
     *
     * @param  FormBuilderInterface $builder The form builder.
     * @param  array                $options The options for this form.
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'schedule.form.title',
            ])
            ->add('location', null, [
                'label' => 'schedule.form.location',
            ])
            ->add('notes', CKEditorType::class, [
                'label' => 'schedule.form.notes',
                'config' => ['toolbar' => 'basic'],
                'required' => false])
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
                'label' => 'schedule.form.active',
                'choices' => [
                    'schedule.form.active_choices.active' => true,
                    'schedule.form.active_choices.inactive' => false,
                ],
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
     * @param  OptionsResolver $resolver The options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Schedule::class,
            'translation_domain' => BkstgScheduleBundle::TRANSLATION_DOMAIN,
        ]);
    }
}
