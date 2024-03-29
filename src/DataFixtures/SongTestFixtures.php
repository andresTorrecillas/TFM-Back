<?php

namespace App\DataFixtures;

use App\Entity\Song;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;

/**
 * @codeCoverageIgnore
 */
class SongTestFixtures extends Fixture
{
    private static FakerGenerator $faker;
    public const NUMBER_SONGS = 5;

    public function load(ObjectManager $manager): void
    {
        self::$faker = FakerFactory::create('es_ES');
        for ($i = 1; $i <= self::NUMBER_SONGS; $i ++){
            $title = ($i == self::NUMBER_SONGS)
                ? "A Eliminar"
                : self::$faker->sentence(1) . " song " . $i;
            $reference = ($i == self::NUMBER_SONGS)
                ? "NjZ-Delete"
                : "NjI5YmE4ZjcwYjJhMw-" . $i;
            $song = new Song($reference);
            $song
                ->setTitle($title)
                ->setLyrics(self::$faker->text());
            $manager->persist($song);
        }

        $manager->flush();
    }
}
