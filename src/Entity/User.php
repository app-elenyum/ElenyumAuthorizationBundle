<?php

namespace Elenyum\Authorization\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Elenyum\Authorization\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['login'], message: 'There is already an account with this login')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const STATUS_PENDING = 'pending'; // в ожидание например для новых пользователей
    public const STATUS_ACTIVE = 'active'; // Пользователь подтвержден
    public const STATUS_BLOCKED = 'blocked'; // Пользователь заблокирован
    public const STATUS_INACTIVE = 'inactive'; // Пользователь не активен

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Length(min: 1, max: 200)]
    #[ORM\Column(length: 180)]
    private ?string $login;

    #[Assert\Length(min: 1, max: 50)]
    #[ORM\Column(length: 50)]
    private ?string $status;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mname = null;

    #[ORM\Column]
    private array $roles;

    #[ORM\Column(length: 64, nullable: false)]
    private string $password;

    #[ORM\Column]
    private DateTimeImmutable $createAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->setCreateAt(new DateTimeImmutable());
        $this->setStatus(self::STATUS_ACTIVE);
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): User
    {
        $this->status = $status;

        return $this;
    }

    public function getFname(): ?string
    {
        return $this->fname;
    }

    public function setFname(?string $fname): User
    {
        $this->fname = $fname;

        return $this;
    }

    public function getLname(): ?string
    {
        return $this->lname;
    }

    public function setLname(?string $lname): User
    {
        $this->lname = $lname;

        return $this;
    }

    public function getMname(): ?string
    {
        return $this->mname;
    }

    public function setMname(?string $mname): User
    {
        $this->mname = $mname;

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

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    public function getCreateAt(): DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(DateTimeImmutable $createAt): User
    {
        $this->createAt = $createAt;

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
