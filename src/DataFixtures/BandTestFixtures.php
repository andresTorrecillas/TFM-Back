<?php

namespace App\DataFixtures;

use App\Entity\Band;
use App\Entity\BandUser;
use App\Entity\Role;
use App\Entity\Song;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
class BandTestFixtures extends Fixture
{
    private const NUMBER_BANDS = 2;

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::NUMBER_BANDS; $i ++){
            $role = new Role();
            $role->setName("testRole-$i")
                ->setUser($manager->find(BandUser::class, 1));
            $manager->persist($role);
            $name = "testBand-$i";
            $song = new Song("VRS-$i");
            $song->setTitle('Linked Song ' . $i);
            $manager->persist($song);
            $band = new Band();
            $band->setName($name)
            ->addRole($role)
            ->addSong($song);
            $manager->persist($band);
        }

        $manager->flush();
    }
}
