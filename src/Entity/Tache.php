<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Enum\StatutActivite;
use App\Repository\TacheRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Tâche opérationnelle rattachée à une Action — dernier niveau porteur de
 * budget (le budget est prévu/exécuté seulement, pas engagé : l'engagement
 * comptable se pilote au niveau Action/Programme).
 */
#[ORM\Entity(repositoryClass: TacheRepository::class)]
#[ORM\Table(name: 'tache')]
#[ApiResource(
    operations: [new GetCollection(), new Get()],
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    normalizationContext: ['groups' => ['api:read']],
)]
class Tache
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

    #[ORM\ManyToOne(targetEntity: Action::class, inversedBy: 'taches')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'action de rattachement est obligatoire.")]
    #[Groups(['api:read', 'api:write'])]
    private ?Action $action = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Le responsable de la tâche est obligatoire.')]
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

    #[ORM\Column(type: 'decimal', precision: 14, scale: 2)]
    #[Assert\PositiveOrZero]
    #[Groups(['api:read', 'api:write'])]
    private string $budgetPrevu = '0.00';

    #[ORM\Column(type: 'decimal', precision: 14, scale: 2)]
    #[Assert\PositiveOrZero]
    #[Groups(['api:read', 'api:write'])]
    private string $budgetExecute = '0.00';

    #[ORM\Column]
    #[Groups(['api:read', 'api:write'])]
    private bool $actif = true;

    #[ORM\OneToMany(mappedBy: 'tache', targetEntity: SousTache::class)]
    private Collection $sousTaches;

    public function __construct()
    {
        $this->sousTaches = new ArrayCollection();
    }

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

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function setAction(?Action $action): static
    {
        $this->action = $action;

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

    public function getBudgetPrevu(): string
    {
        return $this->budgetPrevu;
    }

    public function setBudgetPrevu(string $budgetPrevu): static
    {
        $this->budgetPrevu = $budgetPrevu;

        return $this;
    }

    public function getBudgetExecute(): string
    {
        return $this->budgetExecute;
    }

    public function setBudgetExecute(string $budgetExecute): static
    {
        $this->budgetExecute = $budgetExecute;

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

    /**
     * @return Collection<int, SousTache>
     */
    public function getSousTaches(): Collection
    {
        return $this->sousTaches;
    }

    public function __toString(): string
    {
        return $this->libelle ?? '';
    }
}
