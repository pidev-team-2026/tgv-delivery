<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'L\'ID Commande est requis')]
    #[Assert\Positive(message: 'L\'ID Commande doit être un nombre positif')]
    private ?int $idCommande = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le prix total est requis')]
    #[Assert\PositiveOrZero(message: 'Le prix ne peut pas être négatif')]
    private ?float $totalPrix = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le statut est requis')]
    #[Assert\Choice(choices: ['en attente', 'confirmée', 'expédiée', 'livrée', 'annulée'], message: 'Le statut doit être valide')]
    private ?string $statut = null;

    #[ORM\Column]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column]
    private ?\DateTime $dateMisAjour = null;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\ManyToMany(targetEntity: Produit::class)]
    private Collection $produits;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'La référence est requise')]
    #[Assert\Length(min: 3, max: 30, minMessage: 'La référence doit contenir au moins 3 caractères', maxMessage: 'La référence ne peut pas dépasser 30 caractères')]
    private ?string $reference = null;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCommande(): ?int
    {
        return $this->idCommande;
    }

    public function setIdCommande(int $idCommande): static
    {
        $this->idCommande = $idCommande;

        return $this;
    }

    public function getTotalPrix(): ?float
    {
        return $this->totalPrix;
    }

    public function setTotalPrix(float $totalPrix): static
    {
        $this->totalPrix = $totalPrix;

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

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        $this->produits->removeElement($produit);

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }
}
