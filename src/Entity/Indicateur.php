<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Enum\Periodicite;
use App\Entity\Enum\TypeIndicateur;
use App\Repository\IndicateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Indicateur de performance GAR (cible/valeur de référence), rattaché à un
 * Programme (indicateur d'effet) ou une Action (indicateur d'extrant) —
 * jamais les deux à la fois, voir validerRattachement().
 */
#[ORM\Entity(repositoryClass: IndicateurRepository::class)]
#[ORM\Table(name: 'indicateur')]
#[UniqueEntity(fields: ['code'], message: 'Ce code est déjà utilisé par un autre indicateur.')]
#[ApiResource(
    operations: [new GetCollection(), new Get()],
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    normalizationContext: ['groups' => ['api:read']],
)]
#[Assert\Callback(callback: 'validerRattachement')]
class Indicateur
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

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?string $unite = null;

    #[ORM\Column(length: 20, enumType: TypeIndicateur::class)]
    #[Groups(['api:read', 'api:write'])]
    private ?TypeIndicateur $typeIndicateur = null;

    #[ORM\Column(type: 'decimal', precision: 14, scale: 2, nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?string $valeurReference = null;

    #[ORM\Column(type: 'decimal', precision: 14, scale: 2)]
    #[Assert\NotNull]
    #[Groups(['api:read', 'api:write'])]
    private ?string $valeurCible = null;

    #[ORM\Column(length: 20, enumType: Periodicite::class)]
    #[Groups(['api:read', 'api:write'])]
    private ?Periodicite $periodicite = null;

    #[ORM\ManyToOne(targetEntity: Programme::class, inversedBy: 'indicateurs')]
    #[Groups(['api:read', 'api:write'])]
    private ?Programme $programme = null;

    #[ORM\ManyToOne(targetEntity: Action::class, inversedBy: 'indicateurs')]
    #[Groups(['api:read', 'api:write'])]
    private ?Action $action = null;

    #[ORM\OneToMany(mappedBy: 'indicateur', targetEntity: RealisationIndicateur::class)]
    private Collection $realisations;

    public function __construct()
    {
        $this->realisations = new ArrayCollection();
    }

    public function validerRattachement(ExecutionContextInterface $context): void
    {
        $nombreRattachements = (null !== $this->programme ? 1 : 0) + (null !== $this->action ? 1 : 0);

        if (1 !== $nombreRattachements) {
            $context->buildViolation('Un indicateur doit être rattaché à exactement un Programme ou une Action, jamais les deux ni aucun des deux.')
                ->atPath('programme')
                ->addViolation();
        }
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

    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(?string $unite): static
    {
        $this->unite = $unite;

        return $this;
    }

    public function getTypeIndicateur(): ?TypeIndicateur
    {
        return $this->typeIndicateur;
    }

    public function setTypeIndicateur(TypeIndicateur $typeIndicateur): static
    {
        $this->typeIndicateur = $typeIndicateur;

        return $this;
    }

    public function getValeurReference(): ?string
    {
        return $this->valeurReference;
    }

    public function setValeurReference(?string $valeurReference): static
    {
        $this->valeurReference = $valeurReference;

        return $this;
    }

    public function getValeurCible(): ?string
    {
        return $this->valeurCible;
    }

    public function setValeurCible(string $valeurCible): static
    {
        $this->valeurCible = $valeurCible;

        return $this;
    }

    public function getPeriodicite(): ?Periodicite
    {
        return $this->periodicite;
    }

    public function setPeriodicite(Periodicite $periodicite): static
    {
        $this->periodicite = $periodicite;

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

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function setAction(?Action $action): static
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return Collection<int, RealisationIndicateur>
     */
    public function getRealisations(): Collection
    {
        return $this->realisations;
    }

    public function __toString(): string
    {
        return $this->libelle ?? '';
    }
}
