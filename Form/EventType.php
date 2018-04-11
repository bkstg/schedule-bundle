<?php

namespace Bkstg\ScheduleBundle\Form;

use Bkstg\ScheduleBundle\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start')
            ->add('end')
            ->add('name')
            ->add('location')
            ->add('description')
            ->add('invitations')
            ->add('schedule')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Important' => 'important',
                    'Success' => 'success',
                    'Warning' => 'warning',
                    'Info' => 'info',
                    'Inverse' => 'inverse',
                    'Special' => 'special',
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Event::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'bkstg_schedulebundle_event';
    }
}
