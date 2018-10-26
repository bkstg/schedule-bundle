<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgScheduleBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\ScheduleBundle\Form;

use Bkstg\CoreBundle\Context\ProductionContextProviderInterface;
use Bkstg\CoreBundle\User\MembershipProviderInterface;
use Bkstg\ScheduleBundle\BkstgScheduleBundle;
use Bkstg\ScheduleBundle\Entity\Invitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationType extends AbstractType
{
    private $production_context;
    private $membership_provider;

    /**
     * Create a new invitation form.
     *
     * @param ProductionContextProviderInterface $production_context  The production context service.
     * @param MembershipProviderInterface        $membership_provider The membership provider.
     */
    public function __construct(
        ProductionContextProviderInterface $production_context,
        MembershipProviderInterface $membership_provider
    ) {
        $this->production_context = $production_context;
        $this->membership_provider = $membership_provider;
    }

    /**
     * {@inheritdoc}
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The form options.
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Get the context and membership provider to pass into transformer.
        $context = $this->production_context;
        $provider = $this->membership_provider;

        $builder
            ->add('optional', null, [
                'label' => 'invitation.form.optional',
            ])
            ->add('invitee', ChoiceType::class, [
                'label' => 'invitation.form.invitee',
                'placeholder' => 'invitation.form.choose_invitee',
                'choice_translation_domain' => false,

                // Load all active memberships for a production as choices.
                'choice_loader' => new CallbackChoiceLoader(function () use ($context, $provider) {
                    foreach ($provider->loadActiveMembershipsByProduction($context->getContext()) as $membership) {
                        $member = $membership->getMember();
                        yield $member->__toString() => $member->getUsername();
                    }
                }),
            ])
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @param OptionsResolver $resolver The options resolver.
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invitation::class,
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
        return 'bkstg_invitation';
    }
}
