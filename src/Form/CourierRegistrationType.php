<?php

namespace App\Form\Registration;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\{TextType, ChoiceType};
use Symfony\Component\Form\FormBuilderInterface;

class CourierRegistrationType extends BaseRegistrationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('cin', TextType::class, [
                'label' => 'CIN',
                'attr' => [
                    'placeholder' => 'CIN'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le CIN est obligatoire']),
                    new Assert\Regex([
                        'pattern' => '/^[0-9]{8}$/',
                        'message' => 'Le CIN doit contenir 8 chiffres'
                    ]),
                ],
            ])
            ->add('licenseNumber', TextType::class, [
                'label' => 'Numéro de permis',
                'attr' => [
                    'placeholder' => 'Numéro de permis'
                ],
                'constraints' => [new Assert\NotBlank(['message' => 'Le numéro de permis est obligatoire'])],
            ])
            ->add('vehicleType', ChoiceType::class, [
                'label' => 'Type de véhicule',
                'choices' => [
                    'Moto' => 'motorcycle',
                    'Voiture' => 'car',
                    'Camion' => 'truck',
                ],
                'placeholder' => 'Sélectionner un type de véhicule',
                'constraints' => [new Assert\NotBlank(['message' => 'Le type de véhicule est obligatoire'])],
            ]);
    }
}
