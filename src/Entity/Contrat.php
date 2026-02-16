<?php

namespace App\Entity;

use App\Repository\ContratRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContratRepository::class)]
class Contrat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de début est obligatoire")]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire")]
    #[Assert\GreaterThan(propertyPath: 'dateDebut', message: "La date de fin doit être après la date de début")]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $notificationEnvoyeeAt = null;

    #[ORM\ManyToOne(inversedBy: 'contrats')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le partenaire est obligatoire")]
    private ?Partenaire $partenaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getNotificationEnvoyeeAt(): ?\DateTimeInterface
    {
        return $this->notificationEnvoyeeAt;
    }

    public function setNotificationEnvoyeeAt(?\DateTimeInterface $notificationEnvoyeeAt): static
    {
        $this->notificationEnvoyeeAt = $notificationEnvoyeeAt;
        return $this;
    }

    public function isExpire(): bool
    {
        return $this->dateFin && $this->dateFin < new \DateTime();
    }

    public function isExpirantBientot(int $jours = 30): bool
    {
        if (!$this->dateFin) {
            return false;
        }
        $limite = (new \DateTime())->modify("+{$jours} days");
        return $this->dateFin <= $limite && !$this->isExpire();
    }

    public function getPartenaire(): ?Partenaire
    {
        return $this->partenaire;
    }

    public function setPartenaire(?Partenaire $partenaire): static
    {
        $this->partenaire = $partenaire;
        return $this;
    }
}
