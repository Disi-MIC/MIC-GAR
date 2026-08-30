<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Programme;
use App\Security\Voter\ProgrammeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProgrammeController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Créer un Programme est un acte de gouvernance (rattachement à un Axe
     * stratégique, désignation du RProg) réservé à l'autorité ministérielle —
     * contrairement à l'édition, qui revient ensuite au RProg désigné (voir
     * update(), gardé par ProgrammeVoter).
     */
    #[IsGranted('ROLE_AUTORITE')]
    #[Route('/api/programmes', name: 'api_programme_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $programme = $this->serializer->deserialize($request->getContent(), Programme::class, 'json', ['groups' => ['api:write']]);

        $violations = $this->validator->validate($programme);
        if (\count($violations) > 0) {
            return $this->violationsResponse($violations);
        }

        $this->em->persist($programme);
        $this->em->flush();

        return $this->json($programme, JsonResponse::HTTP_CREATED, [], ['groups' => ['api:read']]);
    }

    #[Route('/api/programmes/{id}', name: 'api_programme_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(Programme $programme, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(ProgrammeVoter::EDIT, $programme);

        $this->serializer->deserialize($request->getContent(), Programme::class, 'json', [
            'groups' => ['api:write'],
            'object_to_populate' => $programme,
        ]);

        $violations = $this->validator->validate($programme);
        if (\count($violations) > 0) {
            return $this->violationsResponse($violations);
        }

        $this->em->flush();

        return $this->json($programme, JsonResponse::HTTP_OK, [], ['groups' => ['api:read']]);
    }

    #[IsGranted('ROLE_AUTORITE')]
    #[Route('/api/programmes/{id}', name: 'api_programme_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Programme $programme): JsonResponse
    {
        if (!$programme->getActions()->isEmpty() || !$programme->getIndicateurs()->isEmpty()) {
            return $this->json(['errors' => ['programme' => 'Impossible de supprimer ce programme : des actions ou indicateurs y sont encore rattachés.']], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->remove($programme);
        $this->em->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
