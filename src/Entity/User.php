<?php

namespace Elenyum\Authorization\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Elenyum\Authorization\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['login'], message: 'There is already an account with this login')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['POST_user', 'GET_user', 'PUT_user'])]
    private ?int $id = null;

    #[Assert\Length(min: 1, max: 200)]
    #[ORM\Column(length: 180)]
    #[Groups(['Default', 'POST_user', 'GET_user', 'PUT_user'])]
    private ?string $login;

    #[ORM\Column(name: 'name', type: 'string', nullable: true)]
    #[Groups(['Default', 'POST_user', 'GET_user', 'PUT_user'])]
    public ?string $name = null;

    #[Assert\Length(min: 1, max: 50)]
    #[ORM\Column(length: 50)]
    #[Groups(['Default', 'GET_user'])]
    private ?UserStatus $status;

    #[ORM\Column]
    #[Groups(['Default', 'GET_user'])]
    private array $roles;

    #[ORM\Column(length: 64, nullable: false)]
    #[Groups(['Default', 'POST_user', 'GET_user', 'PUT_user'])]
    private string $password;

    #[ORM\Column]
    #[Groups(['inner', 'GET_user'])]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    #[Groups(['inner', 'GET_user'])]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->setCreatedAt(new DateTimeImmutable());
        $this->setStatus(UserStatus::Active);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(?string $login): User
    {
        $this->login = $login;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): User
    {
        $this->name = $name;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status->value;
    }

    public function setStatus(UserStatus|string|null $status): User
    {
        if ($status instanceof UserStatus) {
            $this->status = $status;
        }

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): User
    {
        $this->roles = $roles;

        return $this;
    }
    
    public function addRole(string $role): void
    {
        $this->roles = array_merge([$role], $this->roles);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): User
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): User
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->login;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
