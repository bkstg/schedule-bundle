<?php

namespace Bkstg\ScheduleBundle\Form;

use Bkstg\CoreBundle\Context\ProductionContextProviderInterface;
use Bkstg\CoreBundle\User\MembershipProviderInterface;
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

    public function __construct(
        ProductionContextProviderInterface $production_context,
        MembershipProviderInterface $membership_provider
    ) {
        $this->production_context = $production_context;
        $this->membership_provider = $membership_provider;
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $context = $this->production_context;
        $provider = $this->membership_provider;

        $builder
            ->add('optional')
            ->add('invitee', ChoiceType::class, [
                'choice_loader' => new CallbackChoiceLoader(function () use ($context, $provider) {
                    $return = [];
                    foreach ($provider->loadMembershipsByGroup($context->getContext()) as $membership) {
                        $member = $membership->getMember();
                        $return[$member->__toString()] = $member->getUsername();
                    }
                    return $return;
                })
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Invitation::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'bkstg_schedulebundle_invitation';
    }
}
