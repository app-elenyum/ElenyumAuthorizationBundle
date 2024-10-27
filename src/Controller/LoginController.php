<?php

namespace Elenyum\Authorization\Controller;

use Elenyum\OpenAPI\Attribute\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Attributes as OA;

#[Tag(name: 'auth')]

#[OA\RequestBody(

    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "username", type: "string", example: "username"),
            new OA\Property(property: "password", type: "string", example: "password"),
        ],
        type: 'object'
    )
)]
#[OA\Response(
    response: 200,
    description: 'return jwt authorization token',
    content: new OA\JsonContent(properties: [
        new OA\Property(property: 'token', type: 'string', default: 'ASd121233afsf142ads...'),
    ]),
)]
class LoginController extends AbstractController
{
    public function __invoke()
    {

    }
}
