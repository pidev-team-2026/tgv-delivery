<?php

namespace App\Entity;

use App\Repository\LivreurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LivreurRepository::class)]
#[ORM\Table(name: 'livreur')]
#[ORM\HasLifecycleCallbacks]
class Livreur
{
    // ── STATUTS ──────────────────────────────
    const STATUT_DISPONIBLE = 'disponible';
    const STATUT_OCCUPE     = 'occupe';
    const STATUT_INACTIF    = 'inactif';

    // ── TYPES ─────────────────────────────────
    const TYPE_PROPRE      = 'propre';       // livreur de l'entreprise
    const TYPE_PARTENAIRE  = 'partenaire';   // livreur externe/partenaire

    // ── VÉHICULES ─────────────────────────────
    const VEHICULE_MOTO    = 'moto';
    const VEHICULE_VOITURE = 'voiture';
    const VEHICULE_VELO    = 'velo';
    const VEHICULE_CAMION  = 'camion';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 20)]
    private ?string $telephone = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    /** disponible | occupe | inactif */
    #[ORM\Column(length: 20, options: ['default' => 'disponible'])]
    private string $statut = self::STATUT_DISPONIBLE;

    /** propre | partenaire */
    #[ORM\Column(length: 20, options: ['default' => 'propre'])]
    private string $type = self::TYPE_PROPRE;

    /** moto | voiture | velo | camion */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $vehicule = null;

    /** Plaque d'immatriculation */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $immatriculation = null;

    /** Zone(s) couverte(s) : ex "Tunis, Ariana, Ben Arous" */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $zonesCouvertes = null;

    /** Nom de la société partenaire (si type=partenaire) */
    #[ORM\Column(length: 150, nullable: true)]
    private ?string $societePartenaire = null;

    /** Photo/avatar URL */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    /** Note moyenne (0-5) */
    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $note = null;

    /** Nombre de livraisons effectuées */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $nombreLivraisons = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateModification = null;

    /** Commandes actuellement assignées à ce livreur */
    #[ORM\OneToMany(mappedBy: 'livreur', targetEntity: Commande::class)]
    private Collection $commandes;

    public function __construct()
    {
        $this->commandes     = new ArrayCollection();
        $this->dateCreation  = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->dateModification = new \DateTimeImmutable();
    }

    // ── Getters / Setters ─────────────────────

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }

    public function getNomComplet(): string { return $this->prenom . ' ' . $this->nom; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(string $telephone): static { $this->telephone = $telephone; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static { $this->email = $email; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getStatutLibelle(): string
    {
        return match($this->statut) {
            self::STATUT_DISPONIBLE => 'Disponible',
            self::STATUT_OCCUPE     => 'Occupé',
            self::STATUT_INACTIF    => 'Inactif',
            default                 => $this->statut,
        };
    }

    public function isDisponible(): bool { return $this->statut === self::STATUT_DISPONIBLE; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getTypeLibelle(): string
    {
        return match($this->type) {
            self::TYPE_PROPRE     => 'Livreur interne',
            self::TYPE_PARTENAIRE => 'Partenaire',
            default               => $this->type,
        };
    }

    public function getVehicule(): ?string { return $this->vehicule; }
    public function setVehicule(?string $vehicule): static { $this->vehicule = $vehicule; return $this; }

    public function getVehiculeLibelle(): string
    {
        return match($this->vehicule) {
            self::VEHICULE_MOTO    => '🏍️ Moto',
            self::VEHICULE_VOITURE => '🚗 Voiture',
            self::VEHICULE_VELO    => '🚲 Vélo',
            self::VEHICULE_CAMION  => '🚚 Camion',
            default                => $this->vehicule ?? '—',
        };
    }

    public function getImmatriculation(): ?string { return $this->immatriculation; }
    public function setImmatriculation(?string $v): static { $this->immatriculation = $v; return $this; }

    public function getZonesCouvertes(): ?string { return $this->zonesCouvertes; }
    public function setZonesCouvertes(?string $v): static { $this->zonesCouvertes = $v; return $this; }

    public function getSocietePartenaire(): ?string { return $this->societePartenaire; }
    public function setSocietePartenaire(?string $v): static { $this->societePartenaire = $v; return $this; }

    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $v): static { $this->photo = $v; return $this; }

    public function getNote(): ?float { return $this->note; }
    public function setNote(?float $v): static { $this->note = $v; return $this; }

    public function getNombreLivraisons(): int { return $this->nombreLivraisons; }
    public function setNombreLivraisons(int $v): static { $this->nombreLivraisons = $v; return $this; }
    public function incrementLivraisons(): static { $this->nombreLivraisons++; return $this; }

    public function getDateCreation(): ?\DateTimeImmutable { return $this->dateCreation; }
    public function getDateModification(): ?\DateTimeImmutable { return $this->dateModification; }

    /** @return Collection<int, Commande> */
    public function getCommandes(): Collection { return $this->commandes; }
}
