<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du produit est requis')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Le nom doit contenir au moins 3 caractères', maxMessage: 'Le nom ne peut pas dépasser 255 caractères')]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description est requise')]
    #[Assert\Length(min: 10, minMessage: 'La description doit contenir au moins 10 caractères')]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le prix est requis')]
    #[Assert\Positive(message: 'Le prix doit être supérieur à 0')]
    private ?float $prix = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La catégorie est requise')]
    #[Assert\Choice(
        choices: ['Entrées', 'Plats principaux', 'Desserts', 'Boissons', 'Snacks', 'Autres'], 
        message: 'La catégorie doit être valide'
    )]
    private ?string $categorie = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le statut est requis')]
    #[Assert\Choice(
        choices: ['disponible', 'rupture', 'bientot_disponible', 'archive'], 
        message: 'Le statut doit être valide'
    )]
    private ?string $statut = 'disponible';

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le stock est requis')]
    #[Assert\PositiveOrZero(message: 'Le stock ne peut pas être négatif')]
    private ?int $stock = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'La promotion doit être entre 0 et 100%')]
    private ?int $promotion = 0; // pourcentage de réduction

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $dateMisAjour = null;

    // Méthodes automatiques pour les dates
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->dateCreation = new \DateTime();
        $this->dateMisAjour = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->dateMisAjour = new \DateTime();
    }

    // Getters et Setters
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;
        return $this;
    }

    public function getPromotion(): ?int
    {
        return $this->promotion;
    }

    public function setPromotion(?int $promotion): static
    {
        $this->promotion = $promotion;
        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function getDateMisAjour(): ?\DateTime
    {
        return $this->dateMisAjour;
    }

    // Méthode utile pour calculer le prix après promotion
    public function getPrixApresPromotion(): float
    {
        if ($this->promotion > 0) {
            return $this->prix * (1 - $this->promotion / 100);
        }
        return $this->prix;
    }

    // Méthode pour vérifier la disponibilité
    public function isDisponible(): bool
    {
        return $this->statut === 'disponible' && $this->stock > 0;
    }
}