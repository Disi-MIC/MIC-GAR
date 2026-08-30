<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Indicateur;
use App\Security\Voter\ActionVoter;
use App\Security\Voter\ProgrammeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IndicateurController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/api/indicateurs', name: 'api_indicateur_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $indicateur = $this->serializer->deserialize($request->getContent(), Indicateur::class, 'json', ['groups' => ['api:write']]);

        $this->denyAccessUnlessGrantedSurRattachement($indicateur);

        $violations = $this->validator->validate($indicateur);
        if (\count($violations) > 0) {
            return $this->violationsResponse($violations);
        }

        $this->em->persist($indicateur);
        $this->em->flush();

        return $this->json($indicateur, JsonResponse::HTTP_CREATED, [], ['groups' => ['api:read']]);
    }

    #[Route('/api/indicateurs/{id}', name: 'api_indicateur_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(Indicateur $indicateur, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGrantedSurRattachement($indicateur);

        $this->serializer->deserialize($request->getContent(), Indicateur::class, 'json', [
            'groups' => ['api:write'],
            'object_to_populate' => $indicateur,
        ]);

        $violations = $this->validator->validate($indicateur);
        if (\count($violations) > 0) {
            return $this->violationsResponse($violations);
        }

        $this->em->flush();

        return $this->json($indicateur, JsonResponse::HTTP_OK, [], ['groups' => ['api:read']]);
    }

    #[Route('/api/indicateurs/{id}', name: 'api_indicateur_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Indicateur $indicateur): JsonResponse
    {
        $this->denyAccessUnlessGrantedSurRattachement($indicateur);

        if (!$indicateur->getRealisations()->isEmpty()) {
            return $this->json(['errors' => ['indicateur' => 'Impossible de supprimer cet indicateur : des réalisations y sont encore rattachées.']], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->remove($indicateur);
        $this->em->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Un Indicateur est rattaché à un Programme (effet) ou une Action
     * (extrant) — voir Indicateur::validerRattachement() — donc l'autorisation
     * suit celle du Voter correspondant plutôt qu'un Voter dédié.
     */
    private function denyAccessUnlessGrantedSurRattachement(Indicateur $indicateur): void
    {
        if (null !== $indicateur->getProgramme()) {
            $this->denyAccessUnlessGranted(ProgrammeVoter::EDIT, $indicateur->getProgramme());

            return;
        }

        if (null !== $indicateur->getAction()) {
            $this->denyAccessUnlessGranted(ActionVoter::EDIT, $indicateur->getAction());

            return;
        }

        throw $this->createAccessDeniedException("L'indicateur doit être rattaché à un programme ou une action.");
    }
}
