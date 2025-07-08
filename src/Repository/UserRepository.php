<?php

namespace Elenyum\Authorization\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Elenyum\Authorization\Entity\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Найти записи и подсчитать общее количество с учетом фильтров.
     *
     * @param array|null $filters
     * @param array|null $orderBy JSON-строка с сортировкой (например, {"login": "ASC"})
     * @param int $limit Количество записей на страницу
     * @param int $offset Смещение для пагинации
     * @return array Массив с ключами 'items' (записи) и 'total' (общее количество)
     */
    public function findWithCount(?array $filters, ?array $orderBy, int $limit, int $offset): array
    {
        $filters = $filters ?? [];
        $orderBy = $orderBy ?? [];

        // Основной QueryBuilder
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');

        // Применяем фильтры и получаем параметры
        $this->applyFilters($qb, $filters, 'f_');

        // Применяем сортировку
        foreach ($orderBy as $field => $direction) {
            $qb->addOrderBy("u.{$field}", $direction);
        }

        // Применяем пагинацию
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getArrayResult();

        // QueryBuilder для подсчета
        $countQb = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from(User::class, 'u');

        $this->applyFilters($countQb, $filters, 'c_');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            $items,
            $total
        ];
    }

    /**
     * Применяет фильтры к QueryBuilder и возвращает массив параметров
     *
     * @param QueryBuilder $qb
     * @param array $filters
     * @param string $prefix Префикс для параметров (должен отличаться у каждого QueryBuilder)
     * @return void Параметры, примененные к QueryBuilder
     */
    private function applyFilters(QueryBuilder $qb, array $filters, string $prefix): void
    {
        $parameters = [];
        $i = 0;

        foreach ($filters as $field => $condition) {
            $paramName = $prefix.$i++;

            $qb->andWhere("u.{$field} LIKE :{$paramName}");
            $parameters[$paramName] = '%'.$condition.'%';
        }

        foreach ($parameters as $key => $value) {
            $qb->setParameter($key, $value);
        }
    }
}
