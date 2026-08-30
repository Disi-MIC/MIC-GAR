<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\AxeStrategiqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Axe stratégique (ou Mission), niveau le plus haut de la nomenclature
 * budgétaire sénégalaise (Axe/Mission > Programme > Action > Activité) —
 * regroupe plusieurs Programmes autour d'une même orientation stratégique.
 */
#[ORM\Entity(repositoryClass: AxeStrategiqueRepository::class)]
#[ORM\Table(name: 'axe_strategique')]
#[UniqueEntity(fields: ['code'], message: 'Ce code est déjà utilisé par un autre axe stratégique.')]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/axes-strategiques'),
        new Get(uriTemplate: '/axes-strategiques/{id}'),
    ],
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    normalizationContext: ['groups' => ['api:read']],
)]
class AxeStrategique
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

    #[ORM\Column]
    #[Groups(['api:read', 'api:write'])]
    private bool $actif = true;

    #[ORM\OneToMany(mappedBy: 'axeStrategique', targetEntity: Programme::class)]
    private Collection $programmes;

    public function __construct()
    {
        $this->programmes = new ArrayCollection();
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
     * @return Collection<int, Programme>
     */
    public function getProgrammes(): Collection
    {
        return $this->programmes;
    }

    public function __toString(): string
    {
        return $this->libelle ?? '';
    }
}
