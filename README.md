
# ElenyumAuthorizationBundle

**ElenyumAuthorizationBundle** предоставляет механизм для создания сущности `User`, настройки авторизации пользователей и добавления атрибутов для контроллеров, позволяющих управлять доступом к методам.

## Установка

Установите пакет с помощью Composer:

```bash
composer require elenyum/authorization
```

### Требования

Этот пакет требует следующие зависимости:
- PHP >= 8.1
- Symfony компоненты:
    - `symfony/console` ^5.4|^6.0|^7.0
    - `symfony/framework-bundle` ^5.4.24|^6.0|^7.0
    - `symfony/options-resolver` ^7.0
    - `symfony/property-info` ^7.0
    - `symfony/validator` ^7.0
- `zircote/swagger-php` ^4.2.15
- `lexik/jwt-authentication-bundle` v3.1.0

## Конфигурация

Дополнительная конфигурация не требуется. Однако, перед использованием необходимо добавить конфигурацию в `doctrine.yaml` для активации маппинга сущностей:

```yaml
doctrine:
    orm:
        mappings:
            ElenyumAuthorizationBundle:
                is_bundle: true
                alias: ElenyumAuthorizationBundle
```

Затем запустите миграции для создания необходимых таблиц:

```bash
php bin/console doctrine:migrations:migrate
```

## Использование атрибута `Auth`

Этот пакет добавляет атрибут `Auth`, который можно использовать в контроллерах для ограничения доступа:

```php
use Elenyum\Authorization\Attribute\Auth;
use App\Entity\Figure;

#[Auth(name: 'Bearer', model: Figure::class)]
public function someAction()
{
    // Логика действия
}
```

- `name`: Имя метода авторизации (используется в документации).
- `model`: Класс сущности, к которой будет применено ограничение по доступу на основе ролей.

## Настройка бизнес-логики доступа через Voter

Для более гибкой настройки правил доступа к сущностям рекомендуется использовать `Voter` в Symfony. Это позволяет реализовать проверку, которая выходит за рамки базовой проверки ролей и может учитывать дополнительные бизнес-правила, например, ограничение доступа только к записям, созданным текущим пользователем.

Пример создания Voter для проверки владельца записи:

```php
namespace App\Security\Voter;

use App\Entity\Figure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class FigureVoter extends Voter
{
    private $security;

    public function __construct(Security $security)
    {
        this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT']) && $subject instanceof Figure;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Проверка, что пользователь является владельцем записи
        return $subject->getOwnerId() === $user->getId();
    }
}
```

### Применение Voter

Чтобы использовать `Voter`, вызовите его через `isGranted` в контроллере или настройте атрибут для проверки:

```php
if (!$this->isGranted('EDIT', $figure)) {
    throw $this->createAccessDeniedException('Access denied.');
}
```

Использование `Voter` помогает отделить бизнес-логику доступа от основного авторизационного механизма, поддерживая принцип единой ответственности и повышая читаемость и масштабируемость кода.
