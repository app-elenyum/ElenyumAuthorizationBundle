# ElenyumAuthorizationBundle

**ElenyumAuthorizationBundle** provides a mechanism for creating the `User` entity, configuring user authorization, and adding attributes to controllers to manage access to methods.

## Installation

Install the package using Composer:

```bash
composer require elenyum/authorization
```

### Requirements

This package requires the following dependencies:
- PHP >= 8.1
- Symfony components:
  - `symfony/console` ^5.4|^6.0|^7.0
  - `symfony/framework-bundle` ^5.4.24|^6.0|^7.0
  - `symfony/options-resolver` ^7.0
  - `symfony/property-info` ^7.0
  - `symfony/validator` ^7.0
- `zircote/swagger-php` ^4.2.15
- `lexik/jwt-authentication-bundle` v3.1.0

## Configuration

No additional configuration is required. However, before use, you need to add configuration to `doctrine.yaml` to activate entity mapping:

```yaml
doctrine:
    orm:
        mappings:
            ElenyumAuthorizationBundle:
                is_bundle: true
                alias: ElenyumAuthorizationBundle
```

Then run migrations to create the necessary tables:

```bash
php bin/console doctrine:migrations:migrate
```

## Using the `Auth` Attribute

This package adds the `Auth` attribute, which can be used in controllers to restrict access:

```php
use Elenyum\Authorization\Attribute\Auth;
use App\Entity\Figure;

#[Auth(name: 'Bearer', model: Figure::class)]
public function someAction()
{
    // Action logic
}
```

- `name`: The name of the authorization method (used in documentation).
- `model`: The entity class to which the access restriction will be applied based on roles.

## Configuring Business Logic Access with Voter

For more flexible access rules to entities, it is recommended to use a `Voter` in Symfony. This allows you to implement checks that go beyond basic role verification and can consider additional business rules, such as restricting access to records created by the current user.

Example of creating a Voter to check record ownership:

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
        $this->security = $security;
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

        // Check if the user is the owner of the record
        return $subject->getOwnerId() === $user->getId();
    }
}
```

### Applying Voter

To use the `Voter`, call it via `isGranted` in the controller or configure the attribute for verification:

```php
if (!$this->isGranted('EDIT', $figure)) {
    throw $this->createAccessDeniedException('Access denied.');
}
```

Using a `Voter` helps separate business access logic from the main authorization mechanism, adhering to the single responsibility principle and improving code readability and scalability.

