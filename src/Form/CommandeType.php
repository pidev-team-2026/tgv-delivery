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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'required' => true,
                'attr' => ['placeholder' => 'Référence automatique']
            ])
            ->add('totalPrix', NumberType::class, [
                'label' => 'Prix Total (€)',
                'required' => true,
                'scale' => 2,
                'attr' => ['placeholder' => '0.00']
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut de la Commande',
                'choices' => [
                    'En attente' => 'en_attente',
                    'Confirmée' => 'confirmee',
                    'En préparation' => 'en_preparation',
                    'Prête' => 'prete',
                    'En livraison' => 'en_livraison',
                    'Livrée' => 'livree',
                    'Annulée' => 'annulee',
                ],
                'required' => true,
            ])
            ->add('nomClient', TextType::class, [
                'label' => 'Nom du Client',
                'required' => true,
                'attr' => ['placeholder' => 'Nom complet du client']
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'required' => true,
                'attr' => ['placeholder' => '+33 1 23 45 67 89']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => ['placeholder' => 'client@email.com']
            ])
            ->add('adresseLivraison', TextareaType::class, [
                'label' => 'Adresse de Livraison',
                'required' => true,
                'attr' => ['rows' => 3, 'placeholder' => 'Adresse complète de livraison']
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'required' => true,
                'attr' => ['placeholder' => 'Paris']
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code Postal',
                'required' => true,
                'attr' => ['placeholder' => '75000']
            ])
            ->add('modePaiement', ChoiceType::class, [
                'label' => 'Mode de Paiement',
                'choices' => [
                    'Espèces' => 'especes',
                    'Carte bancaire' => 'carte',
                    'Mobile Money' => 'mobile_money',
                    'Virement bancaire' => 'virement',
                ],
                'required' => true,
            ])
            ->add('paiementEffectue', CheckboxType::class, [
                'label' => 'Paiement effectué',
                'required' => false,
            ])
            ->add('fraisLivraison', NumberType::class, [
                'label' => 'Frais de Livraison (€)',
                'required' => false,
                'scale' => 2,
                'attr' => ['placeholder' => '0.00']
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => ['rows' => 3, 'placeholder' => 'Notes supplémentaires']
            ])
            ->add('dateLivraisonSouhaitee', DateTimeType::class, [
                'label' => 'Date de Livraison Souhaitée',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('livreur', TextType::class, [
                'label' => 'Livreur',
                'required' => false,
                'attr' => ['placeholder' => 'Nom du livreur']
            ])
            ->add('produits', EntityType::class, [
                'label' => 'Produits de la Commande',
                'class' => Produit::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'required' => false,
                'attr' => ['class' => 'select2']
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


