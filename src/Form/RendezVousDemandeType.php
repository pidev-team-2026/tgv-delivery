<?php

namespace App\Form;

use App\Entity\Commercant;
use App\Entity\RendezVous;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire Front Office : demande de RDV par l'utilisateur (sans choix d'état).
 */
class RendezVousDemandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ;

        if (!$options['commercant_fixed']) {
            $builder->add('commercant', EntityType::class, [
                'class' => Commercant::class,
                'choice_label' => function (Commercant $c) {
                    return $c->getNom() . ' (' . ($c->getMetier() ?? '—') . ')';
                },
                'label' => 'Commerçant',
                'placeholder' => 'Choisir un commerçant',
            ]);
        }

        $builder
            ->add('emailDemandeur', EmailType::class, [
                'label' => 'Votre email (pour recevoir la réponse)',
                'required' => true,
            ])
            ->add('dateRdv', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et heure souhaitées',
            ])
            ->add('message', TextareaType::class, ['label' => 'Message / Détails de la demande'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
            'commercant_fixed' => false,
        ]);

        $resolver->setAllowedTypes('commercant_fixed', 'bool');
    }
}
