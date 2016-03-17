<?php

namespace Bkstg\ScheduleBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ScheduleItemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('datetimeStart', 'datetime', array(
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'label' => 'Start',
            ))
            ->add('datetimeEnd', 'datetime', array(
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'label' => 'End',
            ))
            ->add('scene')
            ->add('notes')
            ->add('fullCompany', 'checkbox', array(
                'required' => false,
            ))
            ->add('fullCast', 'checkbox', array(
                'required' => false,
            ))
            ->add('fullCrew', 'checkbox', array(
                'required' => false,
            ))
            ->add('called');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bkstg\ScheduleBundle\Entity\ScheduleItem'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bkstg_schedulebundle_scheduleitem';
    }
}
