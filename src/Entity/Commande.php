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
    private ?string $reference = null;

    #[ORM\Column]
    private ?float $totalPrix = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['en_attente','confirmee','en_preparation','prete','en_livraison','livree','annulee'])]
    private ?string $statut = 'en_attente';

    #[ORM\Column(length: 100)]
    private ?string $nomClient = null;

    #[ORM\Column(length: 20)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $adresseLivraison = null;

    #[ORM\Column(length: 100)]
    private ?string $ville = null;

    #[ORM\Column(length: 10)]
    private ?string $codePostal = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['especes','carte','mobile_money','virement'])]
    private ?string $modePaiement = null;

    #[ORM\Column]
    private ?bool $paiementEffectue = false;

    #[ORM\Column(nullable: true)]
    private ?float $fraisLivraison = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    // ── NOUVEAUX CHAMPS ───────────────────────────

    /** Code promo saisi par le client */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $codePromo = null;

    /** Montant de la remise en TND */
    #[ORM\Column(nullable: true)]
    private ?float $remise = 0;

    /** Gouvernorat sélectionné */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $gouvernorat = null;

    /** Estimation livraison en minutes */
    #[ORM\Column(nullable: true)]
    private ?int $estimationLivraison = null;

    // ─────────────────────────────────────────────

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

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->dateCreation = new \DateTime();
        $this->dateMisAjour = new \DateTime();
        if ($this->reference === null) $this->generateReference();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void { $this->dateMisAjour = new \DateTime(); }

    private function generateReference(): void
    {
        $this->reference = 'CMD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    // Getters & Setters
    public function getId(): ?int { return $this->id; }
    public function getReference(): ?string { return $this->reference; }
    public function setReference(string $r): static { $this->reference = $r; return $this; }
    public function getTotalPrix(): ?float { return $this->totalPrix; }
    public function setTotalPrix(float $t): static { $this->totalPrix = $t; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $s): static { $this->statut = $s; return $this; }
    public function getNomClient(): ?string { return $this->nomClient; }
    public function setNomClient(string $n): static { $this->nomClient = $n; return $this; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(string $t): static { $this->telephone = $t; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $e): static { $this->email = $e; return $this; }
    public function getAdresseLivraison(): ?string { return $this->adresseLivraison; }
    public function setAdresseLivraison(string $a): static { $this->adresseLivraison = $a; return $this; }
    public function getVille(): ?string { return $this->ville; }
    public function setVille(string $v): static { $this->ville = $v; return $this; }
    public function getCodePostal(): ?string { return $this->codePostal; }
    public function setCodePostal(string $c): static { $this->codePostal = $c; return $this; }
    public function getModePaiement(): ?string { return $this->modePaiement; }
    public function setModePaiement(string $m): static { $this->modePaiement = $m; return $this; }
    public function isPaiementEffectue(): ?bool { return $this->paiementEffectue; }
    public function setPaiementEffectue(bool $p): static { $this->paiementEffectue = $p; return $this; }
    public function getFraisLivraison(): ?float { return $this->fraisLivraison; }
    public function setFraisLivraison(?float $f): static { $this->fraisLivraison = $f; return $this; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $n): static { $this->notes = $n; return $this; }

    // Nouveaux
    public function getCodePromo(): ?string { return $this->codePromo; }
    public function setCodePromo(?string $c): static { $this->codePromo = $c; return $this; }
    public function getRemise(): ?float { return $this->remise; }
    public function setRemise(?float $r): static { $this->remise = $r; return $this; }
    public function getGouvernorat(): ?string { return $this->gouvernorat; }
    public function setGouvernorat(?string $g): static { $this->gouvernorat = $g; return $this; }
    public function getEstimationLivraison(): ?int { return $this->estimationLivraison; }
    public function setEstimationLivraison(?int $e): static { $this->estimationLivraison = $e; return $this; }

    public function getDateLivraisonSouhaitee(): ?\DateTime { return $this->dateLivraisonSouhaitee; }
    public function setDateLivraisonSouhaitee(?\DateTime $d): static { $this->dateLivraisonSouhaitee = $d; return $this; }
    public function getDateLivraisonEffective(): ?\DateTime { return $this->dateLivraisonEffective; }
    public function setDateLivraisonEffective(?\DateTime $d): static { $this->dateLivraisonEffective = $d; return $this; }
    public function getLivreur(): ?string { return $this->livreur; }
    public function setLivreur(?string $l): static { $this->livreur = $l; return $this; }
    public function getProduits(): Collection { return $this->produits; }
    public function addProduit(Produit $p): static { if (!$this->produits->contains($p)) $this->produits->add($p); return $this; }
    public function removeProduit(Produit $p): static { $this->produits->removeElement($p); return $this; }
    public function getDateCreation(): ?\DateTime { return $this->dateCreation; }
    public function getDateMisAjour(): ?\DateTime { return $this->dateMisAjour; }

    public function getMontantTotal(): float
    {
        return ($this->totalPrix ?? 0) + ($this->fraisLivraison ?? 0) - ($this->remise ?? 0);
    }

    public function getStatutLibelle(): string
    {
        return match($this->statut) {
            'en_attente'     => 'En attente',
            'confirmee'      => 'Confirmée',
            'en_preparation' => 'En préparation',
            'prete'          => 'Prête',
            'en_livraison'   => 'En livraison',
            'livree'         => 'Livrée',
            'annulee'        => 'Annulée',
            default          => $this->statut,
        };
    }
}
