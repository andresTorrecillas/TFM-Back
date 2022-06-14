<?php

namespace App\DataFixtures;

use App\Entity\Song;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestFixtures extends Fixture
{
    private static FakerGenerator $faker;
    private const NUMBER_SONGS = 5;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        self::$faker = FakerFactory::create('es_ES');
        for ($i = 1; $i <= self::NUMBER_SONGS; $i ++){
            $title = ($i == self::NUMBER_SONGS)
                ? "A Eliminar"
                : self::$faker->sentence(3) . " song " . $i;
            $reference = ($i == self::NUMBER_SONGS)
                ? "NjZ-Delete"
                : "NjI5YmE4ZjcwYjJhMw-" . $i;
            $song = new Song($reference);
            $song
                ->setTitle($title)
                ->setLyrics(self::$faker->text());
            $manager->persist($song);
        }

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
