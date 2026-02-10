<?php

namespace App\Form;

use App\Entity\Reclamation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Sujet de la réclamation', 'maxlength' => 200],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['class' => 'form-control', 'rows' => 5, 'placeholder' => 'Décrivez votre réclamation en détail...'],
            ]);

        if ($options['include_user']) {
            $builder->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getName() . ' (' . $user->getEmail() . ')';
                },
                'label' => 'Compte',
                'attr' => ['class' => 'form-select'],
                'placeholder' => '-- Choisir un compte --',
            ]);
        }

        if ($options['include_status']) {
            $builder->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => Reclamation::STATUSES,
                'attr' => ['class' => 'form-select'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
            'include_user' => false,
            'include_status' => false,
        ]);

        $resolver->setAllowedTypes('include_user', 'bool');
        $resolver->setAllowedTypes('include_status', 'bool');
    }
}
