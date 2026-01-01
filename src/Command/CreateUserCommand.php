<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a new user',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        // Ask for email
        $emailQuestion = new Question('Email: ');
        $emailQuestion->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Invalid email address');
            }
            return $answer;
        });
        $email = $helper->ask($input, $output, $emailQuestion);

        // Ask for username
        $usernameQuestion = new Question('Username: ');
        $usernameQuestion->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \RuntimeException('Username cannot be empty');
            }
            return $answer;
        });
        $username = $helper->ask($input, $output, $usernameQuestion);

        // Ask for password
        $passwordQuestion = new Question('Password: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setValidator(function ($answer) {
            if (strlen($answer) < 6) {
                throw new \RuntimeException('Password must be at least 6 characters');
            }
            return $answer;
        });
        $password = $helper->ask($input, $output, $passwordQuestion);

        // Ask if admin
        $isAdminQuestion = new ConfirmationQuestion('Is admin? (y/n) [n]: ', false);
        $isAdmin = $helper->ask($input, $output, $isAdminQuestion);

        // Create user
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Set roles
        if ($isAdmin) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        // Persist
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf(
            'User "%s" created successfully%s',
            $username,
            $isAdmin ? ' with ROLE_ADMIN' : ''
        ));

        return Command::SUCCESS;
    }
}
