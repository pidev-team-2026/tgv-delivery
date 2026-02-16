<?php

namespace App\Form;

use App\Entity\Commercant;
use App\Entity\RendezVous;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateRdv', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et heure',
                'attr' => [
                    'min' => (new \DateTime('+1 hour'))->format('Y-m-d\TH:i'),
                ],
            ])
            ->add('emailDemandeur', EmailType::class, [
                'label' => 'Email du demandeur',
            ])
            ->add('message', TextareaType::class, ['label' => 'Message'])
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'EN_ATTENTE',
                    'Confirmé' => 'CONFIRME',
                    'Annulé' => 'ANNULE',
                ],
                'label' => 'État',
            ])
            ->add('commercant', EntityType::class, [
                'class' => Commercant::class,
                'choice_label' => function (Commercant $c) {
                    return $c->getNom() . ' (' . ($c->getMetier() ?? '—') . ')';
                },
                'label' => 'Commerçant',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }
}
