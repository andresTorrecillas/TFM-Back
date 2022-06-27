<?php

namespace App\DataFixtures;

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
        for ($i = 1; $i <= self::NUMBER_OF_CONCERTS; $i ++){
            $name = ($i == self::NUMBER_OF_CONCERTS)
                ? "Z: A Eliminar"
                : "Concert " . $i;
            $concert = new Concert();
            $concert
                ->setName($name)
                ->setAddress('c/Concierto');
            $manager->persist($concert);
        }

        $manager->flush();
    }
}
