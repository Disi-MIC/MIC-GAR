<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Enum\StatutActivite;
use App\Repository\ActionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Action budgétaire, déclinaison opérationnelle d'un Programme (nomenclature
 * LFI). Porte son propre responsable et sa propre enveloppe budgétaire,
 * distincts de ceux du Programme parent.
 */
#[ORM\Entity(repositoryClass: ActionRepository::class)]
#[ORM\Table(name: 'action')]
#[UniqueEntity(fields: ['code'], message: 'Ce code est déjà utilisé par une autre action.')]
#[ApiResource(
    operations: [new GetCollection(), new Get()],
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    normalizationContext: ['groups' => ['api:read']],
)]
class Action
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

    #[ORM\ManyToOne(targetEntity: Programme::class, inversedBy: 'actions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le programme de rattachement est obligatoire.')]
    #[Groups(['api:read', 'api:write'])]
    private ?Programme $programme = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "Le responsable de l'action est obligatoire.")]
    #[Assert\Email]
    #[Groups(['api:read', 'api:write'])]
    private ?string $responsableEmail = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?string $responsableNom = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?string $responsablePrenom = null;

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

    #[ORM\OneToMany(mappedBy: 'action', targetEntity: Tache::class)]
    private Collection $taches;

    #[ORM\OneToMany(mappedBy: 'action', targetEntity: Indicateur::class)]
    private Collection $indicateurs;

    public function __construct()
    {
        $this->taches = new ArrayCollection();
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

    public function getProgramme(): ?Programme
    {
        return $this->programme;
    }

    public function setProgramme(?Programme $programme): static
    {
        $this->programme = $programme;

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
     * @return Collection<int, Tache>
     */
    public function getTaches(): Collection
    {
        return $this->taches;
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
