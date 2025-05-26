<?php

namespace Elenyum\Authorization\Controller;

use Elenyum\Authorization\Service\UserServiceInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    public function __invoke(Request $request, UserServiceInterface $service): JsonResponse
    {
        if (!in_array("user", $this->getUser()->getRoles())) {
            throw $this->createAccessDeniedException();
        }

        try {
            $id = (int)$request->get('id');
            if (!empty($id) && $request->getMethod() === Request::METHOD_PUT) {
                $user = $service->update($id, $request->getContent());

                return $this->json([
                    'success' => true,
                    'data' => $user,
                ]);
            } elseif ($request->getMethod() === Request::METHOD_POST) {
                $user = $service->add($request->getContent());

                return $this->json([
                    'success' => true,
                    'data' => $user,
                ]);
            } else {
                return $this->json([
                    'success' => true,
                    'error' => 'Undefined method: '.$request->getMethod(),
                ]);
            }
        } catch (Exception $e) {
            return $this->json([
                'success' => true,
                'error' => $e->getMessage(),
            ]);
        }
    }
}