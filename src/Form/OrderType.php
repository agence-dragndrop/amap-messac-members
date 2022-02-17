<?php

namespace App\Form;

use App\Entity\Order;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la commande',
                'help' => 'Champ affiché sur l\'interface utilisateur.',
                'required' => true
            ])
            ->add('deliveryDate', DateType::class, [
                'label' => 'Date de livraison',
                'widget' => 'single_text',
                'help' => 'Champ affiché sur l\'interface utilisateur.',
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
