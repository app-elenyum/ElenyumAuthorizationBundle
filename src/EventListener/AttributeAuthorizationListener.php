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
        $roles = ['public'];
        if (!empty($user) && !empty($user->getRoles())) {
            $roles = array_merge($roles, $user->getRoles());
        }

        foreach ($attributes as $attribute) {
            $model = $attribute->model;

            //@todo надо провреять доступ не только entity но и к id при удаление и обнавление записи
            $method = $event->getRequest()->getMethod();
            $groups = [];
            if ($method === 'DELETE' || $method === 'PUT') {
                $groups = $this->getGroupsById($model);
            } else {
                $groups = $this->getGroupsFromAllProperties($model, $method);
            }
            if (!empty($groups) && !in_array('default', $groups)) {
                $rolesIntersect = array_intersect($groups, $roles);
                if (empty($rolesIntersect)) {
                    throw new AccessDeniedHttpException('Role denied. Authorization required.');
                }
            }
        }
    }

    /**
     * @param string $model
     * @return array
     * @throws \ReflectionException
     */
    private function getGroupsById(string $model): array
    {
        $reflectionClass = new ReflectionClass($model);

        // Проверяем, существует ли свойство id
        if (!$reflectionClass->hasProperty('id')) {
            return [];
        }

        $property = $reflectionClass->getProperty('id');
        $attributeGroups = $property->getAttributes(Groups::class);

        // Извлекаем группы из атрибутов, если они есть
        if (empty($attributeGroups)) {
            return [];
        }

        $groups = array_map(fn($attr) => $attr->getArguments()[0] ?? [], $attributeGroups);

        return $this->removeHttpPrefixes(array_unique(array_merge(...$groups)));
    }

    /**
     * @param string $model
     * @param string|null $method
     * @return array
     * @throws \ReflectionException
     */
    private function getGroupsFromAllProperties(string $model, ?string $method = null): array
    {
        $reflectionClass = new ReflectionClass($model);
        $groups = [];

        foreach ($reflectionClass->getProperties() as $property) {
            $attributeGroups = $property->getAttributes(Groups::class);

            if (!empty($attributeGroups)) {
                $propertyGroups = array_map(fn($attr) => $attr->getArguments()[0] ?? [], $attributeGroups);
                $flattenedGroups = array_merge(...$propertyGroups);

                if ($method) {
                    $filteredGroups = array_filter($flattenedGroups, fn($group) => str_starts_with($group, strtoupper($method) . '_'));
                    $groups = array_merge($groups, $filteredGroups);
                } else {
                    $groups = array_merge($groups, $flattenedGroups);
                }
            }
        }

        return array_unique($this->removeHttpPrefixes($groups));
    }

    /**
     * @param array $groups
     * @return array
     */
    private function removeHttpPrefixes(array $groups): array
    {
        return array_map(fn($group) => preg_replace('/^(DELETE_|PUT_|POST_|GET_)/', '', $group), $groups);
    }
}