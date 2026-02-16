<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du Produit',
                'required' => true,
                'attr' => ['placeholder' => 'Entrez le nom du produit']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['rows' => 4, 'placeholder' => 'Décrivez le produit en détail']
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (TND)',
                'required' => true,
                'scale' => 2,
                'attr' => ['placeholder' => '0.00']
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du produit',
                'required' => false,
                'mapped' => false,
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Entrées' => 'Entrées',
                    'Plats principaux' => 'Plats principaux',
                    'Desserts' => 'Desserts',
                    'Boissons' => 'Boissons',
                    'Snacks' => 'Snacks',
                    'Autres' => 'Autres',
                ],
                'required' => true,
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Disponible' => 'disponible',
                    'En rupture' => 'rupture',
                    'Bientôt disponible' => 'bientot_disponible',
                    'Archivé' => 'archive',
                ],
                'required' => true,
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock (unités)',
                'required' => true,
                'attr' => ['placeholder' => 'Quantité en stock']
            ])
            ->add('promotion', IntegerType::class, [
                'label' => 'Promotion (%)',
                'required' => false,
                'attr' => ['placeholder' => '0', 'min' => 0, 'max' => 100]
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


