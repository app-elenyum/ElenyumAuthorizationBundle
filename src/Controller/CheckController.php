<?php

namespace Elenyum\Authorization\Controller;


use Elenyum\Authorization\Entity\User;
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
    description: 'Check auth success',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', default: 'ok'),
        new OA\Property(property: 'success', type: 'boolean', default: true),
    ]),
)]
#[OA\Response(
    response: 417,
    description: 'Check auth error',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string', default: 'Error message'),
        new OA\Property(property: 'success', type: 'boolean', default: false),
    ]),
)]
class CheckController extends AbstractController
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User|null $item */
        $item = $this->getUser();
        try {
            if($item instanceof User) {
                return $this->json([
                    'message' => 'ok',
                    'success' => true,
                    'user' => [
                        'id' => $item->getId(),
                        'login' => $item->getLogin(),
                        'createAt' => $item->getCreateAt(),
                        'roles' => $item->getRoles()
                    ]
                ], Response::HTTP_OK);
            } else {
                return $this->json([
                    'message' => 'ok',
                    'success' => false
                ]);
            }
        } catch (Throwable $e) {
            return $this->json([
                'message' => $e->getMessage(),
                'success' => false,
            ], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}