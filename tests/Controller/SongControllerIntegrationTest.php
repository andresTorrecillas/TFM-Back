<?php

namespace App\Tests\Controller;

use App\Entity\Song;
use App\Controller\SongController;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Symfony\Component\HttpFoundation\Response;

class SongControllerIntegrationTest extends WebTestCase
{
    private static FakerGenerator $faker;
    protected static KernelBrowser $client;

    public function setUp(): void
    {
        self::$client = static::createClient();
    }

    public static function setUpBeforeClass(): void
    {
        self::$faker = FakerFactory::create('es_ES');
    }

    public function testCreateSongIT()
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

    public function testCreateSongIdentityDbErrorIT()
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
        self::assertResponseIsSuccessful();
        self::$client->request(
            Request::METHOD_POST,
            SongController::ROOT_PATH,
            [],
            [],
            [],
            json_encode($song)
        );
        $response = self::$client->getResponse();
        $receivedData = json_decode($response->getContent(),true);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJson($response->getContent());
        self::assertArrayHasKey("message", $receivedData[0], "JSON hasn't got the right format");
        self::assertArrayHasKey("status_code", $receivedData[0], "JSON hasn't got the right format");
    }

    public function testCreateSongDbErrorIT()
    {
        $song = new Song();
        $song->setTitle(self::$faker->sentence(3))->setLyrics(self::$faker->text());
        $_ENV["DATABASE_URL"] = "mysql://root:@127.0.0.1:3306/wrong?serverVersion=5.7&charset=utf8mb4";
        self::$client->request(
            Request::METHOD_POST,
            SongController::ROOT_PATH,
            [],
            [],
            [],
            json_encode($song)
        );
        $response = self::$client->getResponse();
        $receivedData = json_decode($response->getContent(), true);
        self::assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);
        self::assertJson($response->getContent());
        self::assertArrayHasKey("message", $receivedData[0], "JSON hasn't got the right format");
        self::assertArrayHasKey("status_code", $receivedData[0], "JSON hasn't got the right format");

    }
}
