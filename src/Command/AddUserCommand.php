<?php

namespace Elenyum\Authorization\Command;

use Doctrine\ORM\EntityManagerInterface;
use Elenyum\Authorization\Entity\User;
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
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate specification in OpenAPI, format: json')
            ->addOption('name', 'na', InputOption::VALUE_OPTIONAL, 'name user', null)
            ->addOption('login', 'l', InputOption::VALUE_OPTIONAL, 'email login', null)
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'password user', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $input->getOption('name');
        $login = $input->getOption('login');
        $password = $input->getOption('password');

        if (!$name || !$login || !$password) {
            $io->error('Name, email, and password are required.');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setFname($name);
        $user->setLname($name);
        $user->setLogin($login);
        $user->setRoles(['adm']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('User added successfully.');

        return Command::SUCCESS;
    }
}