<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Enum\StatutActivite;
use App\Repository\ProgrammeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Programme budgétaire (nomenclature LFI sénégalaise), rattaché à un Axe
 * stratégique. Porte le Responsable de Programme (RProg) et l'enveloppe
 * budgétaire de premier niveau (prévu/engagé/exécuté) — voir Action/Tâche/
 * SousTache pour la déclinaison opérationnelle.
 *
 * Le RProg est stocké en instantané (email/nom/prénom) plutôt qu'en clé
 * étrangère vers un compte : MIC-GAR n'a pas de base d'utilisateurs propre,
 * l'identité vient du JWT SSO émis par GERM (voir src/Security/JwtAuthenticator)
 * et n'est donc pas jointe en base.
 */
#[ORM\Entity(repositoryClass: ProgrammeRepository::class)]
#[ORM\Table(name: 'programme')]
#[UniqueEntity(fields: ['code'], message: 'Ce code est déjà utilisé par un autre programme.')]
#[ApiResource(
    operations: [new GetCollection(), new Get()],
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    normalizationContext: ['groups' => ['api:read']],
)]
class Programme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    #[Groups(['api:read', 'api:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['api:read', 'api:write'])]
    private ?string $libelle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: AxeStrategique::class, inversedBy: 'programmes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'axe stratégique de rattachement est obligatoire.")]
    #[Groups(['api:read', 'api:write'])]
    private ?AxeStrategique $axeStrategique = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Range(min: 2000, max: 2100)]
    #[Groups(['api:read', 'api:write'])]
    private ?int $exerciceBudgetaire = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Le responsable de programme (RProg) est obligatoire.')]
    #[Assert\Email]
    #[Groups(['api:read', 'api:write'])]
    private ?string $rprogEmail = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?string $rprogNom = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?string $rprogPrenom = null;

    #[ORM\Column(type: 'decimal', precision: 14, scale: 2)]
    #[Assert\PositiveOrZero]
    #[Groups(['api:read', 'api:write'])]
    private string $budgetPrevu = '0.00';

    #[ORM\Column(type: 'decimal', precision: 14, scale: 2)]
    #[Assert\PositiveOrZero]
    #[Groups(['api:read', 'api:write'])]
    private string $budgetEngage = '0.00';

    #[ORM\Column(type: 'decimal', precision: 14, scale: 2)]
    #[Assert\PositiveOrZero]
    #[Groups(['api:read', 'api:write'])]
    private string $budgetExecute = '0.00';

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?\DateTimeImmutable $dateDebut = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\Column(length: 20, enumType: StatutActivite::class)]
    #[Groups(['api:read', 'api:write'])]
    private StatutActivite $statut = StatutActivite::PLANIFIE;

    #[ORM\Column]
    #[Groups(['api:read', 'api:write'])]
    private bool $actif = true;

    #[ORM\OneToMany(mappedBy: 'programme', targetEntity: Action::class)]
    private Collection $actions;

    #[ORM\OneToMany(mappedBy: 'programme', targetEntity: Indicateur::class)]
    private Collection $indicateurs;

    public function __construct()
    {
        $this->actions = new ArrayCollection();
        $this->indicateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
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

    public function getAxeStrategique(): ?AxeStrategique
    {
        return $this->axeStrategique;
    }

    public function setAxeStrategique(?AxeStrategique $axeStrategique): static
    {
        $this->axeStrategique = $axeStrategique;

        return $this;
    }

    public function getExerciceBudgetaire(): ?int
    {
        return $this->exerciceBudgetaire;
    }

    public function setExerciceBudgetaire(int $exerciceBudgetaire): static
    {
        $this->exerciceBudgetaire = $exerciceBudgetaire;

        return $this;
    }

    public function getRprogEmail(): ?string
    {
        return $this->rprogEmail;
    }

    public function setRprogEmail(string $rprogEmail): static
    {
        $this->rprogEmail = $rprogEmail;

        return $this;
    }

    public function getRprogNom(): ?string
    {
        return $this->rprogNom;
    }

    public function setRprogNom(?string $rprogNom): static
    {
        $this->rprogNom = $rprogNom;

        return $this;
    }

    public function getRprogPrenom(): ?string
    {
        return $this->rprogPrenom;
    }

    public function setRprogPrenom(?string $rprogPrenom): static
    {
        $this->rprogPrenom = $rprogPrenom;

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

    public function getBudgetEngage(): string
    {
        return $this->budgetEngage;
    }

    public function setBudgetEngage(string $budgetEngage): static
    {
        $this->budgetEngage = $budgetEngage;

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
     * @return Collection<int, Action>
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    /**
     * @return Collection<int, Indicateur>
     */
    public function getIndicateurs(): Collection
    {
        return $this->indicateurs;
    }

    public function __toString(): string
    {
        return $this->libelle ?? '';
    }
}
