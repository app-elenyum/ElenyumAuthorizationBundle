<?php

namespace Elenyum\Authorization\Controller;

use Elenyum\Authorization\Entity\User;
use Elenyum\Authorization\Service\UserServiceInterface;
use Elenyum\OpenAPI\Attribute\Model;
use Elenyum\OpenAPI\Attribute\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

#[Tag(name: 'auth')]

#[OA\RequestBody(content: new OA\JsonContent(ref: new Model(type: User::class, options: ['method' => 'POST'])))]
#[OA\Response(
    response: 200,
    description: 'Add user',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', default: 'ok'),
        new OA\Property(property: 'success', type: 'boolean', default: true),
        new OA\Property(property: 'item', ref: new Model(type: User::class, groups: ['user'], options: ['method' => 'GET']
        )),
    ]),
)]
#[OA\Response(
    response: 417,
    description: 'Add item error',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', default: 'Error message'),
        new OA\Property(property: 'success', type: 'boolean', default: false),
    ]),
)]
class RegistrationController extends AbstractController
{
    public function __invoke(Request $request, UserServiceInterface $service): JsonResponse
    {
        try {
            $item = $service->add($request->getContent());

            return $this->json([
                'message' => 'ok',
                'success' => true,
                'item' => [
                    'id' => $item->getId(),
                    'login' => $item->getLogin(),
                    'createdAt' => $item->getCreatedAt()
                ]
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return $this->json([
                'message' => $e->getMessage(),
                'success' => false,
            ], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}