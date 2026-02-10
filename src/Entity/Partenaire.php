<?php

namespace App\Entity;

use App\Repository\PartenaireRepository;
use App\Entity\Zone;  // ✅ AJOUTÉ : Import de Zone
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PartenaireRepository::class)]
class Partenaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;  // ✅ AJOUTÉ : Propriété id avec annotations Doctrine

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire")]
    #[Assert\Length(min: 2, max: 255, minMessage: "Le nom doit contenir au moins 2 caractères")]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type est obligatoire")]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'email est obligatoire")]
    #[Assert\Email(message: "L'email n'est pas valide")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le téléphone est obligatoire")]
    #[Assert\Length(min: 8, max: 20, minMessage: "Le téléphone doit contenir au moins 8 caractères")]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire")]
    private ?string $addresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL du site web n'est pas valide")]
    private ?string $siteweb = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La date de début de contrat est obligatoire")]
    private ?\DateTimeImmutable $datedebutcontrat = null;

    #[ORM\ManyToOne(inversedBy: 'partenaires')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Veuillez sélectionner une zone")]
    private ?Zone $zone = null;  // ✅ CORRIGÉ : Zone avec majuscule

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getAddresse(): ?string
    {
        return $this->addresse;
    }

    public function setAddresse(string $addresse): static
    {
        $this->addresse = $addresse;
        return $this;
    }

    public function getSiteweb(): ?string
    {
        return $this->siteweb;
    }

    public function setSiteweb(string $siteweb): static
    {
        $this->siteweb = $siteweb;
        return $this;
    }

    public function getDatedebutcontrat(): ?\DateTimeImmutable
    {
        return $this->datedebutcontrat;
    }

    public function setDatedebutcontrat(?\DateTimeImmutable $datedebutcontrat): static
    {
        $this->datedebutcontrat = $datedebutcontrat;
        return $this;
    }

    public function getZone(): ?Zone  // ✅ CORRIGÉ : Zone avec majuscule
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): static  // ✅ CORRIGÉ : Zone avec majuscule
    {
        $this->zone = $zone;
        return $this;
    }
}