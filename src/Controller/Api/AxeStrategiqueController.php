<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\AxeStrategique;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Écritures sur les axes stratégiques : ressource de gouvernance de haut
 * niveau, réservée à l'autorité ministérielle — pas de notion de
 * "responsable" comme sur Programme/Action/Tâche (voir AbstractResponsableVoter).
 */
#[IsGranted('ROLE_AUTORITE')]
class AxeStrategiqueController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/api/axes-strategiques', name: 'api_axe_strategique_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $axe = $this->serializer->deserialize($request->getContent(), AxeStrategique::class, 'json', ['groups' => ['api:write']]);

        $violations = $this->validator->validate($axe);
        if (\count($violations) > 0) {
            return $this->violationsResponse($violations);
        }

        $this->em->persist($axe);
        $this->em->flush();

        return $this->json($axe, JsonResponse::HTTP_CREATED, [], ['groups' => ['api:read']]);
    }

    #[Route('/api/axes-strategiques/{id}', name: 'api_axe_strategique_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(AxeStrategique $axe, Request $request): JsonResponse
    {
        $this->serializer->deserialize($request->getContent(), AxeStrategique::class, 'json', [
            'groups' => ['api:write'],
            'object_to_populate' => $axe,
        ]);

        $violations = $this->validator->validate($axe);
        if (\count($violations) > 0) {
            return $this->violationsResponse($violations);
        }

        $this->em->flush();

        return $this->json($axe, JsonResponse::HTTP_OK, [], ['groups' => ['api:read']]);
    }

    #[Route('/api/axes-strategiques/{id}', name: 'api_axe_strategique_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(AxeStrategique $axe): JsonResponse
    {
        if (!$axe->getProgrammes()->isEmpty()) {
            return $this->json(['errors' => ['axe' => 'Impossible de supprimer cet axe stratégique : des programmes y sont encore rattachés.']], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->remove($axe);
        $this->em->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
