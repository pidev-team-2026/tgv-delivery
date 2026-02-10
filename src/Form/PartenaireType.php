<?php

namespace App\Form;

use App\Entity\Partenaire;
use App\Entity\Zone;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartenaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du partenaire',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom du partenaire'
                ]
            ])
            ->add('type', TextType::class, [
                'label' => 'Type',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Restaurant, Magasin, Transporteur'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'exemple@email.com'
                ]
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: +33 6 12 34 56 78'
                ]
            ])
            ->add('addresse', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Adresse complète'
                ]
            ])
            ->add('siteweb', UrlType::class, [
                'label' => 'Site web',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://www.exemple.com'
                ]
            ])
            ->add('datedebutcontrat', DateTimeType::class, [
                'label' => 'Date de début de contrat',
                'widget' => 'single_text',
                'html5' => true,
                'input' => 'datetime_immutable',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'datetime-local'
                ]
            ])
            ->add('zone', EntityType::class, [
                'class' => Zone::class,
                'choice_label' => 'nom',
                'label' => 'Zone',
                'placeholder' => 'Sélectionnez une zone',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Partenaire::class,
        ]);
    }
}