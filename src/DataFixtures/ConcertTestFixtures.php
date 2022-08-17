<?php

namespace App\DataFixtures;

use App\Entity\Band;
use App\Entity\Concert;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
class ConcertTestFixtures extends Fixture
{
    private const NUMBER_OF_CONCERTS = 5;

    public function load(ObjectManager $manager): void
    {
        $band = $manager->find(Band::class, 1);
        for ($i = 1; $i <= self::NUMBER_OF_CONCERTS; $i ++){
            $name = ($i == self::NUMBER_OF_CONCERTS)
                ? "Z: A Eliminar"
                : "Concert " . $i;
            $concert = new Concert();
            $concert
                ->setName($name)
                ->setAddress('c/Concierto')
                ->setBand($band);
            $manager->persist($concert);
        }

        $manager->flush();
    }
}
