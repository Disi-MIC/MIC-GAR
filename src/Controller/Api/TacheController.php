<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Tache;
use App\Security\Voter\ActionVoter;
use App\Security\Voter\TacheVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TacheController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/api/taches', name: 'api_tache_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $tache = $this->serializer->deserialize($request->getContent(), Tache::class, 'json', ['groups' => ['api:write']]);

        if (null === $tache->getAction()) {
            return $this->json(['errors' => ['action' => "L'action de rattachement est obligatoire."]], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->denyAccessUnlessGranted(ActionVoter::EDIT, $tache->getAction());

        $violations = $this->validator->validate($tache);
        if (\count($violations) > 0) {
            return $this->violationsResponse($violations);
        }

        $this->em->persist($tache);
        $this->em->flush();

        return $this->json($tache, JsonResponse::HTTP_CREATED, [], ['groups' => ['api:read']]);
    }

    #[Route('/api/taches/{id}', name: 'api_tache_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(Tache $tache, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(TacheVoter::EDIT, $tache);

        $this->serializer->deserialize($request->getContent(), Tache::class, 'json', [
            'groups' => ['api:write'],
            'object_to_populate' => $tache,
        ]);

        $violations = $this->validator->validate($tache);
        if (\count($violations) > 0) {
            return $this->violationsResponse($violations);
        }

        $this->em->flush();

        return $this->json($tache, JsonResponse::HTTP_OK, [], ['groups' => ['api:read']]);
    }

    #[Route('/api/taches/{id}', name: 'api_tache_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Tache $tache): JsonResponse
    {
        $this->denyAccessUnlessGranted(TacheVoter::EDIT, $tache);

        if (!$tache->getSousTaches()->isEmpty()) {
            return $this->json(['errors' => ['tache' => 'Impossible de supprimer cette tâche : des sous-tâches y sont encore rattachées.']], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->remove($tache);
        $this->em->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
