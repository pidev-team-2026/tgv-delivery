<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idCommande', NumberType::class, [
                'label' => 'ID Commande',
                'required' => true,
            ])
            ->add('totalPrix', NumberType::class, [
                'label' => 'Prix Total (€)',
                'required' => true,
                'scale' => 2,
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut de la Commande',
                'choices' => [
                    'En attente' => 'en attente',
                    'Confirmée' => 'confirmée',
                    'Expédiée' => 'expédiée',
                    'Livrée' => 'livrée',
                    'Annulée' => 'annulée',
                ],
                'required' => true,
            ])
            ->add('dateCreation', DateTimeType::class, [
                'label' => 'Date de Création',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('dateMisAjour', DateTimeType::class, [
                'label' => 'Date de Mise à Jour',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'required' => true,
            ])
            ->add('produits', EntityType::class, [
                'label' => 'Produits de la Commande',
                'class' => Produit::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}


