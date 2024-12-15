<?php

namespace Elenyum\Authorization\EventListener;


use Elenyum\Authorization\Attribute\Auth;
use ReflectionClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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

        $user = $this->security->getUser();

        if (!$user) {
            throw new AccessDeniedHttpException('Access denied. Authorization required.');
        }

        foreach ($attributes as $attribute) {
            $model = $attribute->model;

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
     * Получает группы сущностей из атрибута Groups
     *
     * @param string $entityClass
     * @return array - Группы сущности
     * @throws \ReflectionException
     */
    private function getEntityGroups(string $entityClass): array
    {
        $reflectionClass = new ReflectionClass($entityClass);
        $attributeGroups = $reflectionClass->getAttributes(Groups::class);

        // Проверяем, есть ли хотя бы один атрибут Groups
        if (empty($attributeGroups)) {
            return []; // Возвращаем пустой массив, если атрибутов нет
        }

        // Извлекаем аргументы из всех найденных атрибутов
        $groups = array_map(fn($attr) => $attr->getArguments()[0] ?? [], $attributeGroups);

        // Объединяем все группы в один массив и убираем дубликаты
        return array_unique(array_merge(...$groups));
    }
}