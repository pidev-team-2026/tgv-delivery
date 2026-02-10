<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        'Ouverte' => self::STATUS_OPEN,
        'En cours' => self::STATUS_IN_PROGRESS,
        'Fermée' => self::STATUS_CLOSED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reclamations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur est requis.")]
    private ?User $user = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: "Le sujet est requis.")]
    #[Assert\Length(min: 3, max: 200, minMessage: "Le sujet doit contenir au moins {{ limit }} caractères.")]
    private ?string $subject = null;
//cc
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le message est requis.")]
    #[Assert\Length(min: 10, minMessage: "Le message doit contenir au moins {{ limit }} caractères.")]
    private ?string $message = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_OPEN;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, Reponse>
     */
    #[ORM\OneToMany(targetEntity: Reponse::class, mappedBy: 'reclamation', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $reponses;

    public function __construct()
    {
        $this->reponses = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTime();
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusLabel(): string
    {
        return array_flip(self::STATUSES)[$this->status] ?? $this->status;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Reponse>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setReclamation($this);
        }

        return $this;
    }

    public function removeReponse(Reponse $reponse): static
    {
        if ($this->reponses->removeElement($reponse)) {
            if ($reponse->getReclamation() === $this) {
                $reponse->setReclamation(null);
            }
        }

        return $this;
    }
}
