<?php

namespace App\DataFixtures;

use App\Entity\Song;
use App\Entity\BandUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

/**
 * @codeCoverageIgnore
 */
class UserFixtures extends Fixture
{
    private static FakerGenerator $faker;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new BandUser("Andrés");
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'velaband'
        );
        $user->setPassword($hashedPassword)
            ->setBandName("Velaband");
        $manager->persist($user);

        $manager->flush();
    }
}
