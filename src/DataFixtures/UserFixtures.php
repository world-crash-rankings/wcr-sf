<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $admin = new User();
        $admin->setEmail('admin@wcr.local');
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);

        $manager->flush();
    }
}
