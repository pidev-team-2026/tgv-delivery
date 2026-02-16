<?php

namespace App\Entity;

use App\Repository\CommercantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommercantRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class Commercant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Le nom doit faire au moins {{ limit }} caractères.', maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email {{ value }} n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le numéro de téléphone est obligatoire.')]
    #[Assert\Length(min: 8, max: 20, minMessage: 'Le téléphone doit faire au moins {{ limit }} caractères.', maxMessage: 'Le téléphone ne peut pas dépasser {{ limit }} caractères.')]
    #[Assert\Regex(pattern: '/^[\d\s\+\-\(\)]+$/', message: 'Le numéro de téléphone contient des caractères non autorisés.')]
    private ?string $Numero_telephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'La ville est obligatoire.')]
    #[Assert\Choice(choices: [
        'Tunis',
        'Ariana',
        'Ben Arous',
        'Manouba',
        'Nabeul',
        'Bizerte',
        'Béja',
        'Jendouba',
        'Le Kef',
        'Siliana',
        'Zaghouan',
        'Sousse',
        'Monastir',
        'Mahdia',
        'Kairouan',
        'Kasserine',
        'Sidi Bouzid',
        'Sfax',
        'Gabès',
        'Médenine',
        'Tataouine',
        'Gafsa',
        'Tozeur',
        'Kébili',
    ], message: 'Ville invalide.')]
    private ?string $ville = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'Le métier / service est obligatoire.')]
    #[Assert\Choice(choices: [
        'Mécanicien',
        'Plombier',
        'Électricien',
        'Peintre',
        'Menuisier',
        'Coiffeur / Salon de beauté',
        'Informatique / Réparation PC',
        'Taxi / Transport',
        'Restaurant / Snack',
        'Boulangerie / Pâtisserie',
        'Pharmacie',
        'Autre service',
    ], message: 'Métier invalide.')]
    private ?string $metier = null;

    /**
     * @var Collection<int, RendezVous>
     */
    #[ORM\OneToMany(targetEntity: RendezVous::class, mappedBy: 'commercant', orphanRemoval: true)]
    private Collection $rendezVouses;

    public function __construct()
    {
        $this->rendezVouses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getNumeroTelephone(): ?string
    {
        return $this->Numero_telephone;
    }

    public function setNumeroTelephone(string $Numero_telephone): static
    {
        $this->Numero_telephone = $Numero_telephone;

        return $this;
    }

    public function getMetier(): ?string
    {
        return $this->metier;
    }

    public function setMetier(string $metier): static
    {
        $this->metier = $metier;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * @return Collection<int, RendezVous>
     */
    public function getRendezVouses(): Collection
    {
        return $this->rendezVouses;
    }

    public function addRendezVouse(RendezVous $rendezVouse): static
    {
        if (!$this->rendezVouses->contains($rendezVouse)) {
            $this->rendezVouses->add($rendezVouse);
            $rendezVouse->setCommercant($this);
        }

        return $this;
    }

    public function removeRendezVouse(RendezVous $rendezVouse): static
    {
        if ($this->rendezVouses->removeElement($rendezVouse)) {
            // set the owning side to null (unless already changed)
            if ($rendezVouse->getCommercant() === $this) {
                $rendezVouse->setCommercant(null);
            }
        }

        return $this;
    }
}
