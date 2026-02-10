<?php

namespace App\Form\Registration;

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
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex('/^[0-9]{8}$/'),
                ],
            ])
            ->add('licenseNumber', TextType::class, [
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('vehicleType', ChoiceType::class, [
                'choices' => [
                    'Motorcycle' => 'motorcycle',
                    'Car' => 'car',
                    'Truck' => 'truck',
                ],
                'constraints' => [new Assert\NotBlank()],
            ]);
    }
}
