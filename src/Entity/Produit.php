<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'L\'ID Produit est requis')]
    #[Assert\Positive(message: 'L\'ID Produit doit être un nombre positif')]
    private ?int $idProd = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du produit est requis')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Le nom doit contenir au moins 3 caractères', maxMessage: 'Le nom ne peut pas dépasser 255 caractères')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La description est requise')]
    #[Assert\Length(min: 10, max: 255, minMessage: 'La description doit contenir au moins 10 caractères', maxMessage: 'La description ne peut pas dépasser 255 caractères')]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le prix est requis')]
    #[Assert\PositiveOrZero(message: 'Le prix ne peut pas être négatif')]
    private ?float $prix = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le statut est requis')]
    #[Assert\Choice(choices: ['actif', 'inactif', 'en rupture'], message: 'Le statut doit être valide')]
    private ?string $statut = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le stock est requis')]
    #[Assert\PositiveOrZero(message: 'Le stock ne peut pas être négatif')]
    private ?int $stock = null;

    #[ORM\Column]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column]
    private ?\DateTime $dateMisAjour = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdProd(): ?int
    {
        return $this->idProd;
    }

    public function setIdProd(int $idProd): static
    {
        $this->idProd = $idProd;

        return $this;
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

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateMisAjour(): ?\DateTime
    {
        return $this->dateMisAjour;
    }

    public function setDateMisAjour(\DateTime $dateMisAjour): static
    {
        $this->dateMisAjour = $dateMisAjour;

        return $this;
    }
}
