<?php

namespace App\Form;

use App\Entity\Contrat;
use App\Entity\Partenaire;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContratType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début du contrat',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin du contrat',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
            ]);

        if ($options['with_partenaire']) {
            $builder->add('partenaire', EntityType::class, [
                'class' => Partenaire::class,
                'choice_label' => 'nom',
                'label' => 'Partenaire',
                'placeholder' => 'Sélectionnez un partenaire',
                'attr' => ['class' => 'form-control'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contrat::class,
            'with_partenaire' => true,
        ]);

        $resolver->setAllowedTypes('with_partenaire', 'bool');
    }
}
