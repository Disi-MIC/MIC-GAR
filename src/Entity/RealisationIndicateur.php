<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\RealisationIndicateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Saisie périodique de la valeur réalisée d'un Indicateur (ex: "2026-T3").
 * Une entrée par période — l'historique des saisies successives permet de
 * tracer la trajectoire vers la valeur cible.
 */
#[ORM\Entity(repositoryClass: RealisationIndicateurRepository::class)]
#[ORM\Table(name: 'realisation_indicateur')]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/realisations-indicateur'),
        new Get(uriTemplate: '/realisations-indicateur/{id}'),
    ],
    security: "is_granted('IS_AUTHENTICATED_FULLY')",
    normalizationContext: ['groups' => ['api:read']],
)]
class RealisationIndicateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['api:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Indicateur::class, inversedBy: 'realisations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'indicateur concerné est obligatoire.")]
    #[Groups(['api:read', 'api:write'])]
    private ?Indicateur $indicateur = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    #[Groups(['api:read', 'api:write'])]
    private ?string $periode = null;

    #[ORM\Column(type: 'decimal', precision: 14, scale: 2)]
    #[Assert\NotNull]
    #[Groups(['api:read', 'api:write'])]
    private ?string $valeurRealisee = null;

    #[ORM\Column(type: 'date_immutable')]
    #[Groups(['api:read'])]
    private \DateTimeImmutable $dateSaisie;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['api:read', 'api:write'])]
    private ?string $observations = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    #[Groups(['api:read'])]
    private ?string $saisiParEmail = null;

    public function __construct()
    {
        $this->dateSaisie = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIndicateur(): ?Indicateur
    {
        return $this->indicateur;
    }

    public function setIndicateur(?Indicateur $indicateur): static
    {
        $this->indicateur = $indicateur;

        return $this;
    }

    public function getPeriode(): ?string
    {
        return $this->periode;
    }

    public function setPeriode(string $periode): static
    {
        $this->periode = $periode;

        return $this;
    }

    public function getValeurRealisee(): ?string
    {
        return $this->valeurRealisee;
    }

    public function setValeurRealisee(string $valeurRealisee): static
    {
        $this->valeurRealisee = $valeurRealisee;

        return $this;
    }

    public function getDateSaisie(): \DateTimeImmutable
    {
        return $this->dateSaisie;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): static
    {
        $this->observations = $observations;

        return $this;
    }

    public function getSaisiParEmail(): ?string
    {
        return $this->saisiParEmail;
    }

    public function setSaisiParEmail(?string $saisiParEmail): static
    {
        $this->saisiParEmail = $saisiParEmail;

        return $this;
    }

    public function __toString(): string
    {
        return \sprintf('%s — %s', $this->indicateur, $this->periode ?? '');
    }
}
