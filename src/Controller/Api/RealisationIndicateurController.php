<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\RealisationIndicateur;
use App\Security\JwtRemoteUser;
use App\Security\Voter\ActionVoter;
use App\Security\Voter\ProgrammeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RealisationIndicateurController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/api/realisations-indicateur', name: 'api_realisation_indicateur_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $realisation = $this->serializer->deserialize($request->getContent(), RealisationIndicateur::class, 'json', ['groups' => ['api:write']]);

        if (null === $realisation->getIndicateur()) {
            return $this->json(['errors' => ['indicateur' => "L'indicateur concerné est obligatoire."]], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->denyAccessUnlessGrantedSurIndicateur($realisation);

        $user = $this->getUser();
        if ($user instanceof JwtRemoteUser) {
            $realisation->setSaisiParEmail($user->getEmail());
        }

        $violations = $this->validator->validate($realisation);
        if (\count($violations) > 0) {
            return $this->violationsResponse($violations);
        }

        $this->em->persist($realisation);
        $this->em->flush();

        return $this->json($realisation, JsonResponse::HTTP_CREATED, [], ['groups' => ['api:read']]);
    }

    #[Route('/api/realisations-indicateur/{id}', name: 'api_realisation_indicateur_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(RealisationIndicateur $realisation, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGrantedSurIndicateur($realisation);

        $this->serializer->deserialize($request->getContent(), RealisationIndicateur::class, 'json', [
            'groups' => ['api:write'],
            'object_to_populate' => $realisation,
        ]);

        $violations = $this->validator->validate($realisation);
        if (\count($violations) > 0) {
            return $this->violationsResponse($violations);
        }

        $this->em->flush();

        return $this->json($realisation, JsonResponse::HTTP_OK, [], ['groups' => ['api:read']]);
    }

    #[Route('/api/realisations-indicateur/{id}', name: 'api_realisation_indicateur_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(RealisationIndicateur $realisation): JsonResponse
    {
        $this->denyAccessUnlessGrantedSurIndicateur($realisation);

        $this->em->remove($realisation);
        $this->em->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }

    private function denyAccessUnlessGrantedSurIndicateur(RealisationIndicateur $realisation): void
    {
        $indicateur = $realisation->getIndicateur();

        if (null !== $indicateur?->getProgramme()) {
            $this->denyAccessUnlessGranted(ProgrammeVoter::EDIT, $indicateur->getProgramme());

            return;
        }

        if (null !== $indicateur?->getAction()) {
            $this->denyAccessUnlessGranted(ActionVoter::EDIT, $indicateur->getAction());

            return;
        }

        throw $this->createAccessDeniedException();
    }
}
