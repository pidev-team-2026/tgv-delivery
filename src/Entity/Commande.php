<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, unique: true)]
    #[Assert\NotBlank(message: 'La référence est requise')]
    private ?string $reference = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le prix total est requis')]
    #[Assert\PositiveOrZero(message: 'Le prix ne peut pas être négatif')]
    private ?float $totalPrix = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le statut est requis')]
    #[Assert\Choice(
        choices: ['en_attente', 'confirmee', 'en_preparation', 'prete', 'en_livraison', 'livree', 'annulee'], 
        message: 'Le statut doit être valide'
    )]
    private ?string $statut = 'en_attente';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom du client est requis')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le nom doit contenir au moins 2 caractères')]
    private ?string $nomClient = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le téléphone est requis')]
    #[Assert\Regex(
        pattern: '/^[0-9+\s\-()]+$/',
        message: 'Le numéro de téléphone n\'est pas valide'
    )]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(message: 'L\'email n\'est pas valide')]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'L\'adresse de livraison est requise')]
    #[Assert\Length(min: 10, minMessage: 'L\'adresse doit contenir au moins 10 caractères')]
    private ?string $adresseLivraison = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La ville est requise')]
    private ?string $ville = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'Le code postal est requis')]
    private ?string $codePostal = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le mode de paiement est requis')]
    #[Assert\Choice(
        choices: ['especes', 'carte', 'mobile_money', 'virement'], 
        message: 'Le mode de paiement doit être valide'
    )]
    private ?string $modePaiement = null;

    #[ORM\Column]
    private ?bool $paiementEffectue = false;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'Les frais de livraison ne peuvent pas être négatifs')]
    private ?float $fraisLivraison = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $dateLivraisonSouhaitee = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $dateLivraisonEffective = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $livreur = null;

    #[ORM\ManyToMany(targetEntity: Produit::class)]
    private Collection $produits;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $dateMisAjour = null;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
        $this->generateReference();
    }

    // Méthodes automatiques pour les dates
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->dateCreation = new \DateTime();
        $this->dateMisAjour = new \DateTime();
        if ($this->reference === null) {
            $this->generateReference();
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->dateMisAjour = new \DateTime();
    }

    // Génération automatique de la référence
    private function generateReference(): void
    {
        $this->reference = 'CMD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
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

    public function getNomClient(): ?string
    {
        return $this->nomClient;
    }

    public function setNomClient(string $nomClient): static
    {
        $this->nomClient = $nomClient;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getAdresseLivraison(): ?string
    {
        return $this->adresseLivraison;
    }

    public function setAdresseLivraison(string $adresseLivraison): static
    {
        $this->adresseLivraison = $adresseLivraison;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;
        return $this;
    }

    public function getModePaiement(): ?string
    {
        return $this->modePaiement;
    }

    public function setModePaiement(string $modePaiement): static
    {
        $this->modePaiement = $modePaiement;
        return $this;
    }

    public function isPaiementEffectue(): ?bool
    {
        return $this->paiementEffectue;
    }

    public function setPaiementEffectue(bool $paiementEffectue): static
    {
        $this->paiementEffectue = $paiementEffectue;
        return $this;
    }

    public function getFraisLivraison(): ?float
    {
        return $this->fraisLivraison;
    }

    public function setFraisLivraison(?float $fraisLivraison): static
    {
        $this->fraisLivraison = $fraisLivraison;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getDateLivraisonSouhaitee(): ?\DateTime
    {
        return $this->dateLivraisonSouhaitee;
    }

    public function setDateLivraisonSouhaitee(?\DateTime $dateLivraisonSouhaitee): static
    {
        $this->dateLivraisonSouhaitee = $dateLivraisonSouhaitee;
        return $this;
    }

    public function getDateLivraisonEffective(): ?\DateTime
    {
        return $this->dateLivraisonEffective;
    }

    public function setDateLivraisonEffective(?\DateTime $dateLivraisonEffective): static
    {
        $this->dateLivraisonEffective = $dateLivraisonEffective;
        return $this;
    }

    public function getLivreur(): ?string
    {
        return $this->livreur;
    }

    public function setLivreur(?string $livreur): static
    {
        $this->livreur = $livreur;
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

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function getDateMisAjour(): ?\DateTime
    {
        return $this->dateMisAjour;
    }

    // Méthode utile pour calculer le montant total (prix + frais de livraison)
    public function getMontantTotal(): float
    {
        return $this->totalPrix + ($this->fraisLivraison ?? 0);
    }

    // Méthode pour obtenir un libellé du statut en français
    public function getStatutLibelle(): string
    {
        return match($this->statut) {
            'en_attente' => 'En attente',
            'confirmee' => 'Confirmée',
            'en_preparation' => 'En préparation',
            'prete' => 'Prête',
            'en_livraison' => 'En livraison',
            'livree' => 'Livrée',
            'annulee' => 'Annulée',
            default => $this->statut,
        };
    }
}