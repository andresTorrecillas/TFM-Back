<?php

namespace App\Tests\Controller;

use App\DataFixtures\SongFixtures;
use App\Entity\Song;
use App\Controller\SongController;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Symfony\Component\HttpFoundation\Response;

class SongControllerIntegrationTest extends WebTestCase
{
    private static AbstractDatabaseTool $databaseTool;
    private static FakerGenerator $faker;
    protected static KernelBrowser $client;
    private const ID_PREFIX = "NjI5YmE4ZjcwYjJhMw-";

    public function setUp(): void
    {
        self::$client = static::createClient();
        self::$databaseTool = self::$client->getContainer()->get(DatabaseToolCollection::class)->get();
        self::$databaseTool->loadFixtures([
            SongFixtures::class
        ]);
    }

    public static function setUpBeforeClass(): void
    {
        self::$faker = FakerFactory::create('es_ES');

    }

    public function testGetIT(){
        $id = self::ID_PREFIX . "1";
        self::$client->request(Request::METHOD_GET, SongController::ROOT_PATH . "/" . $id);
        $response = self::$client->getResponse();
        self::assertResponseIsSuccessful();
        self::assertJson($response->getContent());
        $receivedSong = json_decode($response->getContent(), true);
        //self::assertEquals($id, $receivedSong["id"]);
        $this->assertEquals($id, $receivedSong["id"]);
        self::assertArrayHasKey("title", $receivedSong);
        self::assertArrayHasKey("lyrics", $receivedSong);
    }

    public function testGetListIT(){
        self::$client->request(Request::METHOD_GET, SongController::ROOT_PATH);
        $response = self::$client->getResponse();
        self::assertResponseIsSuccessful();
        self::assertJson($response->getContent());
        $receivedSongList = json_decode($response->getContent(), true);
        $index = 1;
        foreach ($receivedSongList as $song){
            self::assertStringEndsWith($index, $song["id"]);
            self::assertArrayHasKey("title", $song);
            self::assertArrayHasKey("lyrics", $song);
            $index ++;
        }
    }

    public function testCreateSongIT()
    {
        $song = new Song(self::ID_PREFIX . "6");
        $song->setTitle(self::$faker->sentence(3))->setLyrics(self::$faker->text());
        self::$client->request(
            Request::METHOD_POST,
            SongController::ROOT_PATH,
            content: json_encode($song)
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
        $song = new Song(self::ID_PREFIX . 7);
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
}
