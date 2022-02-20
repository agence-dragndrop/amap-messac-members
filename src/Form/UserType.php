<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\User;
use App\Repository\MemberRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('isVerified', CheckboxType::class, [
                'required' => false,
                'label' => 'Compte vérifié' ,
                'label_attr' => ['class' => 'checkbox-switch']
            ])
            ->add('member', EntityType::class, [
                'class' => Member::class,
                'label' => 'Compte adhérent',
                'query_builder' => function (MemberRepository $memberRepository) {
                    return $memberRepository->createQueryBuilder('m')
                        ->orderBy('m.email', 'ASC');
                },
                'choice_label' => function(Member $member) {
                    return $member->getEmail()
                        . " (" . $member->getFirstName()
                        . " " . $member->getLastName() . ")";
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
