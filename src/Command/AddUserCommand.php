<?php

namespace Elenyum\Authorization\Command;

use Doctrine\ORM\EntityManagerInterface;
use Elenyum\Authorization\Entity\User;
use Elenyum\Authorization\Entity\UserStatus;
use Elenyum\Authorization\Repository\UserRepository;
use Masterminds\HTML5\Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'elenyum:user:add',
    description: 'Created user in database',
    aliases: ['e:u:a']
)]

class AddUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $repository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate specification in OpenAPI, format: json')
            ->addOption('login', 'l', InputOption::VALUE_REQUIRED, 'email login', null)
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'password user', null)
            ->addOption('status', 's', InputOption::VALUE_OPTIONAL, 'status user', UserStatus::Active->value)
            ->addOption('roles', 'r', InputOption::VALUE_IS_ARRAY|InputOption::VALUE_OPTIONAL, 'roles user', []);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $login = $input->getOption('login');
        $password = $input->getOption('password');
        $status =  UserStatus::from($input->getOption('status'));
        $roles = $input->getOption('roles');

        if (!$login || !$password) {
            $io->error('Name, email, and password are required.');
            return Command::FAILURE;
        }

        if (count($this->repository->findBy(['login' => $login])) > 0) {
            $io->error('User with this login already exists');
            return Command::FAILURE;
        }
        $user = new User();
        $user->setLogin($login);
        $user->setStatus($status);
        $user->setRoles($roles);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('User added successfully.');

        return Command::SUCCESS;
    }
}