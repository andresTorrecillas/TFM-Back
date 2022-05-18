<?php

namespace App\Tests\Controller;

use App\Entity\Song;
use App\Controller\SongController;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;

class SongControllerTest extends WebTestCase
{
    private static FakerGenerator $faker;
    private static KernelBrowser $client;

    public static function setUpBeforeClass(): void
    {
        self::$client = static::createClient();
        self::$faker = FakerFactory::create('es_ES');
    }

    public function testCreateSong()
    {
        $song = new Song();
        $song->setTitle(self::$faker->sentence(3))->setLyrics(self::$faker->text());
        self::$client->request(
            Request::METHOD_POST,
            SongController::ROOT_PATH,
            [],
            [],
            [],
            json_encode($song)
        );
        $response = self::$client->getResponse();
        $receivedSong = json_decode($response->getContent(),true);
        self::assertResponseIsSuccessful();
        self::assertJson($response->getContent());
        self::assertSame($song->getTitle(), $receivedSong["title"]);
        self::assertSame($song->getLyrics(), $receivedSong["lyrics"]);


    }
}
