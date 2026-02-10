<?php

namespace App\Form\Registration;

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
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex('/^[0-9]{8}$/'),
                ],
            ])
            ->add('businessName', TextType::class, [
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('businessType', TextType::class, [
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('location', TextType::class, [
                'constraints' => [new Assert\NotBlank()],
            ]);
    }
}

