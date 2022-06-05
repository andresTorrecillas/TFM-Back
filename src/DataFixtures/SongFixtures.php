<?php

namespace App\DataFixtures;

use App\Entity\Song;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;

class SongFixtures extends Fixture
{
    private static FakerGenerator $faker;
    private const NUMBER_SONGS = 5;

    public function load(ObjectManager $manager): void
    {
        self::$faker = FakerFactory::create('es_ES');
        for ($i = 1; $i <= self::NUMBER_SONGS; $i ++){
            $song = new Song("NjI5YmE4ZjcwYjJhMw-" . $i);
            $song
                ->setTitle(self::$faker->sentence(3) . " song " . $i)
                ->setLyrics(self::$faker->text());
            $manager->persist($song);
        }

        $manager->flush();
    }
}
