<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Action;
use App\Security\Voter\ActionVoter;
use App\Security\Voter\ProgrammeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ActionController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Créer une Action revient au RProg (ou à l'autorité) du Programme
     * parent — vérifié après désérialisation puisque le programme cible
     * vient du corps de la requête (IRI `/api/programmes/{id}`), pas de la
     * route.
     */
    #[Route('/api/actions', name: 'api_action_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $action = $this->serializer->deserialize($request->getContent(), Action::class, 'json', ['groups' => ['api:write']]);

        if (null === $action->getProgramme()) {
            return $this->json(['errors' => ['programme' => 'Le programme de rattachement est obligatoire.']], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->denyAccessUnlessGranted(ProgrammeVoter::EDIT, $action->getProgramme());

        $violations = $this->validator->validate($action);
        if (\count($violations) > 0) {
            return $this->violationsResponse($violations);
        }

        $this->em->persist($action);
        $this->em->flush();

        return $this->json($action, JsonResponse::HTTP_CREATED, [], ['groups' => ['api:read']]);
    }

    #[Route('/api/actions/{id}', name: 'api_action_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(Action $action, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(ActionVoter::EDIT, $action);

        $this->serializer->deserialize($request->getContent(), Action::class, 'json', [
            'groups' => ['api:write'],
            'object_to_populate' => $action,
        ]);

        $violations = $this->validator->validate($action);
        if (\count($violations) > 0) {
            return $this->violationsResponse($violations);
        }

        $this->em->flush();

        return $this->json($action, JsonResponse::HTTP_OK, [], ['groups' => ['api:read']]);
    }

    #[Route('/api/actions/{id}', name: 'api_action_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(Action $action): JsonResponse
    {
        $this->denyAccessUnlessGranted(ActionVoter::EDIT, $action);

        if (!$action->getTaches()->isEmpty() || !$action->getIndicateurs()->isEmpty()) {
            return $this->json(['errors' => ['action' => 'Impossible de supprimer cette action : des tâches ou indicateurs y sont encore rattachés.']], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->em->remove($action);
        $this->em->flush();

        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
