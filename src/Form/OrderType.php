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
                'label' => 'Nom de la commande'
            ])
            ->add('uploadFile', VichFileType::class, [
                'label' => 'Fichier csv associé',
                'required' => false,
                'allow_delete' => true,
            ])
            ->add('deliveryDate', DateType::class, [
                'label' => 'Date de distribution',
                'widget' => 'single_text'
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Commande clôturée le',
                'widget' => 'single_text'
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
