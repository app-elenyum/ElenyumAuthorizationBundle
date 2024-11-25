<?php

namespace Elenyum\Authorization\Entity;

enum UserStatus: string
{
    case Pending = 'pending';   // в ожидание например для новых пользователей
    case Active = 'active';     // Пользователь подтвержден
    case Blocked = 'blocked';   // Пользователь заблокирован
    case Inactive = 'inactive'; // Пользователь не активен
}
