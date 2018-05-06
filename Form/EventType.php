<?php

namespace Bkstg\ScheduleBundle\Form;

use Bkstg\ScheduleBundle\Entity\Event;
use Bkstg\ScheduleBundle\Form\InvitationType;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
            ->add('start', null, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('end', null, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('name')
            ->add('location')
            ->add('description', CKEditorType::class, [
                'required' => false,
                'config' => ['toolbar' => 'basic'],
            ])
            ->add('invitations', CollectionType::class, [
                'entry_type' => InvitationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('type', ChoiceType::class, [
                'required' => false,
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
