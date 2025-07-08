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
        $roles = $this->getUser()?->getRoles() ?? [];
        $requiredRoles = ['user', 'ROLE_CAD_MAIN'];

        if (empty(array_intersect($roles, $requiredRoles))) {
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
            } elseif (!empty($id) && $request->getMethod() === Request::METHOD_DELETE) {
                $service->delete($id);

                return $this->json([
                    'success' => true,
                ]);
            } elseif ($request->getMethod() === Request::METHOD_GET) {
                $query = $request->query->all();
                $offset = $request->get('offset', (int)$request->get('first', 0));
                $limit = $request->get('limit', (int)$request->get('rows', 100));
                $sortField = $request->get('sortField');
                $sortOrder = (int) $request->get('sortOrder', 0) === 1 ? 'ASC' : 'DESC';
                $orderBy = '{}';
                if (!empty($sortField)) {
                    $sortField = lcfirst(str_replace('front', '', $sortField));
                    $orderBy = '{'.$sortField.':'.$sortOrder.'}';
                }
                $filter = '{}';
                if (!empty($query['filters']['global']['value'])) {
                    $filter = "{login:{$query['filters']['global']['value']}}";
                }

                [$items, $total] = $service->getItems($filter, $orderBy, $limit, $offset);

                return $this->json([
                    'success' => true,
                    'items' => $items,
                    'paginator' => [
                        'offset' => $offset,
                        'limit' => $limit,
                        'total' => $total,
                    ],
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'error' => 'Undefined method: '.$request->getMethod(),
                ]);
            }
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}