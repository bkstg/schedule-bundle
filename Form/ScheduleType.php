<?php

namespace Bkstg\ScheduleBundle\Form;

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
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('location')
            ->add('notes', CKEditorType::class, [
                'config' => ['toolbar' => 'basic'],
                'required' => false])
            ->add('colour', ChoiceType::class, [
                'label' => 'Colour',
                'required' => false,
                'choices' => [
                    'Red' => 'important',
                    'Green' => 'success',
                    'Yellow' => 'warning',
                    'Blue' => 'info',
                    'Dark' => 'inverse',
                    'Purple' => 'special',
                ],
            ])
            ->add('active', ChoiceType::class, [
                'choices' => [
                    'Active' => true,
                    'Closed' => false,
                ]
            ])
            ->add('events', CollectionType::class, [
                'entry_type' => ScheduleEventType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Schedule::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'bkstg_schedulebundle_schedule';
    }
}
