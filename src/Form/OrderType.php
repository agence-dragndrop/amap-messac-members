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
            ->add('uploadFile', VichFileType::class, [
                'label' => 'Fichier csv associé',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'retirer',
                'download_uri' => false,
                'help' => 'Le fichier csv est utilisé pour générer et relier chaque commande aux adhérents.
                Si par la suite vous souhaitez supprimer toutes les lignes, pensez également à retirer le fichier téléchargé.'
            ])
            ->add('deliveryDate', DateType::class, [
                'label' => 'Date de distribution',
                'widget' => 'single_text',
                'help' => 'Champ affiché sur l\'interface utilisateur.',
                'required' => true
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Commande clôturée le',
                'widget' => 'single_text',
                'help' => 'Champ non utilisé actuellement.',
                'required' => false
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
