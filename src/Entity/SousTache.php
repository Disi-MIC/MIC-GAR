<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Enum\StatutActivite;
use App\Repository\SousTacheRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Sous-tâche, dernier maillon de la hiérarchie GAR — pas de budget propre
 * (le budget se pilote au niveau Tâche), seulement un suivi d'avancement.
 */
#[ORM\Entity(repositoryClass: SousTacheRepository::class)]
#[ORM\Table(name: 'sous_tache')]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/sous-taches'),
        new Get(uriTemplate: '/sous-taches/{id}'),
    ],
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    normalizationContext: ['groups' => ['api:read']],
)]
class SousTache
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['api:read', 'api:write'])]
    private ?string $libelle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Tache::class, inversedBy: 'sousTaches')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La tâche de rattachement est obligatoire.')]
    #[Groups(['api:read', 'api:write'])]
    private ?Tache $tache = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Le responsable de la sous-tâche est obligatoire.')]
    #[Assert\Email]
    #[Groups(['api:read', 'api:write'])]
    private ?string $responsableEmail = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?string $responsableNom = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?string $responsablePrenom = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?\DateTimeImmutable $dateDebut = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\Column(length: 20, enumType: StatutActivite::class)]
    #[Groups(['api:read', 'api:write'])]
    private StatutActivite $statut = StatutActivite::PLANIFIE;

    #[ORM\Column(type: 'smallint')]
    #[Assert\Range(min: 0, max: 100)]
    #[Groups(['api:read', 'api:write'])]
    private int $avancementPourcentage = 0;

    #[ORM\Column]
    #[Groups(['api:read', 'api:write'])]
    private bool $actif = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTache(): ?Tache
    {
        return $this->tache;
    }

    public function setTache(?Tache $tache): static
    {
        $this->tache = $tache;

        return $this;
    }

    public function getResponsableEmail(): ?string
    {
        return $this->responsableEmail;
    }

    public function setResponsableEmail(string $responsableEmail): static
    {
        $this->responsableEmail = $responsableEmail;

        return $this;
    }

    public function getResponsableNom(): ?string
    {
        return $this->responsableNom;
    }

    public function setResponsableNom(?string $responsableNom): static
    {
        $this->responsableNom = $responsableNom;

        return $this;
    }

    public function getResponsablePrenom(): ?string
    {
        return $this->responsablePrenom;
    }

    public function setResponsablePrenom(?string $responsablePrenom): static
    {
        $this->responsablePrenom = $responsablePrenom;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeImmutable $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeImmutable $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getStatut(): StatutActivite
    {
        return $this->statut;
    }

    public function setStatut(StatutActivite $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getAvancementPourcentage(): int
    {
        return $this->avancementPourcentage;
    }

    public function setAvancementPourcentage(int $avancementPourcentage): static
    {
        $this->avancementPourcentage = $avancementPourcentage;

        return $this;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function __toString(): string
    {
        return $this->libelle ?? '';
    }
}
