<?php

namespace App\Form;

use App\Entity\Reponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Contenu de la réponse',
                'attr' => ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Écrivez votre réponse...'],
            ])
            ->add('author', TextType::class, [
                'label' => 'Auteur',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Admin'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
        ]);
    }
}
