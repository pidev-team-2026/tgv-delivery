<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Commercant;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    public const ETAT_EN_ATTENTE = 'EN_ATTENTE';
    public const ETAT_CONFIRME = 'CONFIRME';
    public const ETAT_ANNULE = 'ANNULE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'La date du rendez-vous est obligatoire.')]
    #[Assert\Type(\DateTimeInterface::class)]
    #[Assert\GreaterThanOrEqual('today', message: 'La date du rendez-vous doit être dans le futur.')]
    private ?\DateTimeInterface $dateRdv = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le message est obligatoire.')]
    #[Assert\Length(min: 5, max: 255, minMessage: 'Le message doit faire au moins {{ limit }} caractères.', maxMessage: 'Le message ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $message = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'état est obligatoire.")]
    #[Assert\Choice(choices: [self::ETAT_EN_ATTENTE, self::ETAT_CONFIRME, self::ETAT_ANNULE], message: "L'état choisi n'est pas valide.")]
    private ?string $etat = null;

    #[ORM\ManyToOne(inversedBy: 'rendezVouses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le commerçant est obligatoire.')]
    private ?Commercant $commercant = null;

    /** Email du demandeur (pour envoi de la notification validation/refus). */
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(message: "L'email {{ value }} n'est pas valide.")]
    private ?string $emailDemandeur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateRdv(): ?\DateTimeInterface
    {
        return $this->dateRdv;
    }

    public function setDateRdv(?\DateTimeInterface $dateRdv): static
    {
        $this->dateRdv = $dateRdv;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;
        return $this;
    }

    public function getCommercant(): ?Commercant
    {
        return $this->commercant;
    }

    public function setCommercant(?Commercant $commercant): static
    {
        $this->commercant = $commercant;
        return $this;
    }

    public function getEmailDemandeur(): ?string
    {
        return $this->emailDemandeur;
    }

    public function setEmailDemandeur(?string $emailDemandeur): static
    {
        $this->emailDemandeur = $emailDemandeur;
        return $this;
    }
}
