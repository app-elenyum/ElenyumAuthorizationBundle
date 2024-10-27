<?php

namespace Elenyum\Authorization\Service;

use Elenyum\Authorization\Entity\User;

interface UserServiceInterface
{
    public function getItems(string $filter, string $orderBy, int $limit, int $offset): array;
    public function add(string $data): User;
    public function update(int $id, string $data): User;
    public function delete(int $id): bool;
}