<?php

namespace App\DataFixtures;

use App\Entity\Song;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SongFixtures extends Fixture
{
    private static FakerGenerator $faker;
    private const NUMBER_SONGS = 30;

    public function load(ObjectManager $manager): void
    {
        self::$faker = FakerFactory::create('es_ES');
        for ($i = 1; $i <= self::NUMBER_SONGS; $i ++){
            $song = new Song("NjI5YmE4ZjcwYjJhMw-$i");
            $song
                ->setTitle("Song $i")
                ->setLyrics(self::$faker->text(5000));
            $manager->persist($song);
        }

        $manager->flush();
    }
}
