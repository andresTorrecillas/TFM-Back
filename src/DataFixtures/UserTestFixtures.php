<?php

namespace App\DataFixtures;

use App\Entity\Song;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @codeCoverageIgnore
 */
class UserTestFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User('test');
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'test_psw'
        );
        $user->setBandName('testBand')
        ->setPassword($hashedPassword);
        $manager->persist($user);

        $manager->flush();
    }
}
