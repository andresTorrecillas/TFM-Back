<?php

namespace App\Tests\Controller;

use App\Controller\UserController;
use App\DataFixtures\TestFixtures;
use App\Entity\Song;
use App\Controller\SongController;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class SongControllerIntegrationTest extends WebTestCase
{
    private static FakerGenerator $faker;
    protected static KernelBrowser $client;
    private const ID_PREFIX = "NjI5YmE4ZjcwYjJhMw-";
    private const ID_DELETE = "NjZ-Delete";

    public function setUp(): void
    {
        self::$client = $this->createClient();
        $databaseTool = self::$client->getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([
            TestFixtures::class
        ]);
        $session = new Session(new MockFileSessionStorage());
        self::$client->getContainer()->set('session', $session);
        $this->login();
    }

    private function login(){
        self::$client->request(
            Request::METHOD_POST,
            UserController::ROOT_PATH . "/login",
            content: json_encode(
                [
                    "userName" => 'test',
                    "password" => base64_encode('test_psw')
                ]
            )
        );
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
        self::assertEquals($id, $receivedSong["id"]);
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
            self::assertArrayHasKey("id", $song);
            if(!str_contains($song["id"], "Delete")) {
                self::assertStringEndsWith($index, $song["id"]);
            }
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
        self::assertArrayHasKey("statusCode", $receivedData[0], "JSON hasn't got the right format");
    }

    public function testDeleteIT(){
        $id = self::ID_DELETE;
        self::$client->request(Request::METHOD_DELETE, SongController::ROOT_PATH . "/" . $id);
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::$client->request(Request::METHOD_GET, SongController::ROOT_PATH . "/" . $id);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testPatchIT(){
        $id = self::ID_PREFIX . "4";
        $updatedLyrics = self::$faker->text();
        self::$client->request(
            Request::METHOD_PATCH,
            SongController::ROOT_PATH . "/" . $id,
            content: json_encode(["lyrics" => $updatedLyrics])
        );
        self::assertResponseIsSuccessful();
        self::$client->request(Request::METHOD_GET, SongController::ROOT_PATH . "/" . $id);
        $response = self::$client->getResponse();
        $receivedData = json_decode($response->getContent(),true);
        self::assertEquals($updatedLyrics, $receivedData["lyrics"]);
    }

    public function testPatchNotFoundIT(){
        $id = self::ID_PREFIX . "12";
        $updatedLyrics = self::$faker->text();
        self::$client->request(
            Request::METHOD_PATCH,
            SongController::ROOT_PATH . "/" . $id,
            content: json_encode(["lyrics" => $updatedLyrics])
        );
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }


}
