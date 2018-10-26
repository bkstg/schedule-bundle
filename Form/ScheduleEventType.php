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
use Bkstg\ScheduleBundle\Entity\Event;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduleEventType extends AbstractType
{
    /**
     * {@inheritdoc}
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options for this form.
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start', null, [
                'label' => 'event.form.start',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('end', null, [
                'label' => 'event.form.end',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('name', null, [
                'label' => 'event.form.name',
            ])
            ->add('description', CKEditorType::class, [
                'label' => 'event.form.description',
                'required' => false,
                'config' => ['toolbar' => 'basic'],
            ])
            ->add('full_company', CheckboxType::class, [
                'label' => 'event.form.full_company',
                'required' => false,
            ])
            ->add('invitations', CollectionType::class, [
                'label' => 'event.form.invitations',
                'entry_type' => InvitationType::class,
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
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'translation_domain' => BkstgScheduleBundle::TRANSLATION_DOMAIN,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'bkstg_schedule_event';
    }
}
