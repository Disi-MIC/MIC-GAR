<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\SousTache;
use App\Security\Voter\SousTacheVoter;
use App\Security\Voter\TacheVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SousTacheController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/api/sous-taches', name: 'api_sous_tache_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $sousTache = $this->serializer->deserialize($request->getContent(), SousTache::class, 'json', ['groups' => ['api:write']]);

        if (null === $sousTache->getTache()) {
            return $this->json(['errors' => ['tache' => 'La tâche de rattachement est obligatoire.']], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->denyAccessUnlessGranted(TacheVoter::EDIT, $sousTache->getTache());

        $violations = $this->validator->validate($sousTache);
        if (\count($violations) > 0) {
            return $this->violationsResponse($violations);
        }

        $this->em->persist($sousTache);
        $this->em->flush();

        return $this->json($sousTache, JsonResponse::HTTP_CREATED, [], ['groups' => ['api:read']]);
    }

    #[Route('/api/sous-taches/{id}', name: 'api_sous_tache_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(SousTache $sousTache, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(SousTacheVoter::EDIT, $sousTache);

        $this->serializer->deserialize($request->getContent(), SousTache::class, 'json', [
            'groups' => ['api:write'],
            'object_to_populate' => $sousTache,
        ]);

        $violations = $this->validator->validate($sousTache);
        if (\count($violations) > 0) {
            return $this->violationsResponse($violations);
        }

        $this->em->flush();

        return $this->json($sousTache, JsonResponse::HTTP_OK, [], ['groups' => ['api:read']]);
    }

    #[Route('/api/sous-taches/{id}', name: 'api_sous_tache_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(SousTache $sousTache): JsonResponse
    {
        $this->denyAccessUnlessGranted(SousTacheVoter::EDIT, $sousTache);

        $this->em->remove($sousTache);
        $this->em->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
