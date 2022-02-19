<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isActive', CheckboxType::class, [
                'required' => false,
                'label' => 'Adhésion active' ,
                'label_attr' => ['class' => 'checkbox-switch']
            ])
            ->add('email')
            ->add('firstName', TextType::class, [
                'required' => false,
                'label' => 'Prénom'
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
                'label' => 'Nom'
            ])
            ->add('phone1', TextType::class, [
                'required' => false,
                'label' => 'Tél.1'
            ])
            ->add('phone2', TextType::class, [
                'required' => false,
                'label' => 'Tél.2'
            ])
            ->add('mobile1', TextType::class, [
                'required' => false,
                'label' => 'Portable 1'
            ])
            ->add('mobile2', TextType::class, [
                'required' => false,
                'label' => 'Portable 2'
            ])
            ->add('address', TextType::class, [
                'required' => false,
                'label' => 'Adresse'
            ])
            ->add('zip', TextType::class, [
                'required' => false,
                'label' => 'Code postal'
            ])
            ->add('city', TextType::class, [
                'required' => false,
                'label' => 'Ville'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}
