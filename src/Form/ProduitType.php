<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idProd', NumberType::class, [
                'label' => 'ID Produit',
                'required' => true,
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom du Produit',
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'required' => true,
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (€)',
                'required' => true,
                'scale' => 2,
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Actif' => 'actif',
                    'Inactif' => 'inactif',
                    'En rupture' => 'en rupture',
                ],
                'required' => true,
            ])
            ->add('stock', NumberType::class, [
                'label' => 'Stock (unités)',
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}


