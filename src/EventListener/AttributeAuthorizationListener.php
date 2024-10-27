<?php

namespace Elenyum\Authorization\EventListener;


use Elenyum\Authorization\Attribute\Auth;
use ReflectionClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Attribute\Groups;


class AttributeAuthorizationListener implements EventSubscriberInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER_ARGUMENTS => ['onKernelControllerArguments', 20]];
    }

    /**
     * @throws \ReflectionException
     */
    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        /** @var Auth[] $attributes */
        if (!\is_array($attributes = $event->getAttributes()[Auth::class] ?? null)) {
            return;
        }

//        $request = $event->getRequest();
//        $arguments = $event->getNamedArguments();
        $controllerBaseName = basename(str_replace('\\', '/', get_class((object) $event->getController())));
        $method = $this->getHttpMethodFromClassName($controllerBaseName);
        $user = $this->security->getUser();

        if (!$user) {
            throw new AccessDeniedHttpException('Access denied. Authorization required.');
        }

        foreach ($attributes as $attribute) {
            $model = $attribute->model;

            if ($method === null) {
                throw new HttpException(404, 'Undefined type for get groups');
            }

            $groups = $this->getEntityGroups($model);
            if (!empty($groups) && !in_array('default', $groups)) {
                $roles = array_intersect($groups, $user->getRoles());
                if (empty($roles)) {
                    throw new AccessDeniedHttpException('Role denied. Authorization required.');
                }
            }
        }
    }

    /**
     * @param string $entityClass
     * @return array -  entity groups
     * @throws \ReflectionException
     */
    private function getEntityGroups(string $entityClass): array
    {
        $reflectionClass = new ReflectionClass($entityClass);

        $attributeGroups = $reflectionClass->getAttributes(Groups::class);
        if (isset($attributeGroups[0]) && isset($attributeGroups[0]?->getArguments()[0])) {
            return $attributeGroups[0]->getArguments()[0];
        }

        return [];
    }

    private function getHttpMethodFromClassName(string $className): ?string
    {
        // Определяем возможные методы
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];

        // Проверяем специальный случай для "List"
        if (str_contains($className, 'List')) {
            return 'GET';
        }

        // Ищем метод в названии класса
        foreach ($methods as $method) {
            if (str_contains($className, $method)) {
                return $method;
            }
        }

        // Возвращаем пустую строку или какое-то значение по умолчанию, если метод не найден
        return null;
    }

    private function getGroupIntersections(array $groups, array $userRoles): array
    {
        // Создаем массив для хранения найденных пересечений
        $intersections = [];

        // Проходим по каждому элементу массива групп
        foreach ($groups as $group) {
            // Проверяем каждый элемент на наличие соответствующего ключа в массиве ролей
            foreach ($userRoles as $role) {
                if (str_contains($group, "$role")) {
                    $intersections[] = $group;
                    break;
                }
            }
        }

        return $intersections;
    }
}