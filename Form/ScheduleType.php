<?php

namespace Bkstg\ScheduleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Bkstg\ScheduleBundle\Form\ScheduleItemType;
use Bkstg\ScheduleBundle\Entity\Schedule;

class ScheduleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'Schedule name'
            ))
            ->add('schedule_items', 'collection', array(
                'type' => new ScheduleItemType(),
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bkstg\ScheduleBundle\Entity\Schedule'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bkstg_schedulebundle_schedule';
    }
}
