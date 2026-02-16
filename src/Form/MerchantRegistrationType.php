<?php

namespace App\Form\Registration;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MerchantRegistrationType extends BaseRegistrationType
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
            ->add('businessName', TextType::class, [
                'label' => 'Nom de l\'entreprise',
                'attr' => [
                    'placeholder' => 'Nom de l\'entreprise'
                ],
                'constraints' => [new Assert\NotBlank(['message' => 'Le nom de l\'entreprise est obligatoire'])],
            ])
            ->add('businessType', TextType::class, [
                'label' => 'Type d\'entreprise',
                'attr' => [
                    'placeholder' => 'Type d\'entreprise'
                ],
                'constraints' => [new Assert\NotBlank(['message' => 'Le type d\'entreprise est obligatoire'])],
            ])
            ->add('location', TextType::class, [
                'label' => 'Localisation',
                'attr' => [
                    'placeholder' => 'Localisation'
                ],
                'constraints' => [new Assert\NotBlank(['message' => 'La localisation est obligatoire'])],
            ]);
    }
}

