<?php

namespace Elenyum\Authorization\Service;


use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Elenyum\Authorization\Entity\User;
use Elenyum\Authorization\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepository $repository,
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function getItems(string $filter, string $orderBy, int $limit, int $offset): array
    {
        $filter = $this->prepareJsonFormat($filter) ?? [];
        $orderBy = $this->prepareJsonFormat($orderBy) ?? [];

        return $this->repository->findWithCount($filter, $orderBy, $limit, $offset);
    }

    /**
     * @param string $json
     * @return array
     */
    private function prepareJsonFormat(string $json): array
    {
        // Удаляем все пробельные символы перед и после двоеточия
        $json = preg_replace('/\s*(:|,)\s*/', '$1', $json);

        // Добавляем кавычки вокруг ключей (предполагается, что ключи могут состоять из букв, цифр и символов подчеркивания)
        $json = preg_replace('/([{\[,]\s*)([a-zA-Z0-9_\.]+)/i', '$1"$2"$3', $json);

        // Добавляем двойные кавычки вокруг строковых значений, которые ещё не заключены в двойные кавычки, но могут быть в одинарных
        $json = preg_replace("/:(\s*)'([^']+)'(\s*[},\]])/", ':$1"$2"$3', $json);

        //Заменяем одинарные на двойные ковычки
        $json = preg_replace('/(\')([a-zA-Z0-9_]+)(\')/', '"$2"', $json);

        // Обрабатываем случаи, где значения не заключены ни в какие кавычки
        $json = preg_replace('/:(\s*)([a-zA-Z0-9_]+)(\s*[},\]])/', ':$1"$2"$3', $json);

        return json_decode($json, true);
    }


    public function add(string $data): User
    {
        $context = [
            'allow_extra_attributes' => true,
        ];

        /** @var User $user */
        $user = $this->serializer->deserialize($data, $this->repository->getClassName(), 'json', $context);

        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $user->getPassword()
            )
        );
        $user->addRole('user');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function update(int $id, string $data): User
    {
        /** @var User $item */
        $item = $this->repository->findOneBy(['id' => $id]);

        $context = [
            AbstractNormalizer::OBJECT_TO_POPULATE => $item,
        ];
        $oldPassword = $item->getPassword();
        /** @var User $user */
        $user = $this->serializer->deserialize($data, $this->repository->getClassName(), 'json', $context);
        if ($oldPassword !== $user->getPassword()) {
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $user->getPassword()
                )
            );
        }
        $user->setUpdatedAt(new DateTimeImmutable());
        $this->entityManager->flush();

        return $user;
    }

    public function delete(int $id): bool
    {
        $item = $this->repository->findOneBy(['id' => $id]);
        $this->entityManager->remove($item);
        $this->entityManager->flush();

        return true;
    }
}