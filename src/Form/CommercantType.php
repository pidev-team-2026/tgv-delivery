<?php

namespace App\Form;

use App\Entity\Commercant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommercantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('numeroTelephone', TelType::class, [
                'label' => 'Téléphone',
                'property_path' => 'Numero_telephone',
            ])
            ->add('ville', ChoiceType::class, [
                'label' => 'Ville',
                'placeholder' => 'Sélectionnez une ville',
                'choices' => [
                    'Tunis' => 'Tunis',
                    'Ariana' => 'Ariana',
                    'Ben Arous' => 'Ben Arous',
                    'Manouba' => 'Manouba',
                    'Nabeul' => 'Nabeul',
                    'Bizerte' => 'Bizerte',
                    'Béja' => 'Béja',
                    'Jendouba' => 'Jendouba',
                    'Le Kef' => 'Le Kef',
                    'Siliana' => 'Siliana',
                    'Zaghouan' => 'Zaghouan',
                    'Sousse' => 'Sousse',
                    'Monastir' => 'Monastir',
                    'Mahdia' => 'Mahdia',
                    'Kairouan' => 'Kairouan',
                    'Kasserine' => 'Kasserine',
                    'Sidi Bouzid' => 'Sidi Bouzid',
                    'Sfax' => 'Sfax',
                    'Gabès' => 'Gabès',
                    'Médenine' => 'Médenine',
                    'Tataouine' => 'Tataouine',
                    'Gafsa' => 'Gafsa',
                    'Tozeur' => 'Tozeur',
                    'Kébili' => 'Kébili',
                ],
            ])
            ->add('metier', ChoiceType::class, [
                'label' => 'Métier / Service',
                'choices' => [
                    'Mécanicien' => 'Mécanicien',
                    'Plombier' => 'Plombier',
                    'Électricien' => 'Électricien',
                    'Peintre' => 'Peintre',
                    'Menuisier' => 'Menuisier',
                    'Coiffeur / Salon de beauté' => 'Coiffeur / Salon de beauté',
                    'Informatique / Réparation PC' => 'Informatique / Réparation PC',
                    'Taxi / Transport' => 'Taxi / Transport',
                    'Restaurant / Snack' => 'Restaurant / Snack',
                    'Boulangerie / Pâtisserie' => 'Boulangerie / Pâtisserie',
                    'Pharmacie' => 'Pharmacie',
                    'Autre service' => 'Autre service',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commercant::class,
        ]);
    }
}
