<?php

namespace App\Tests\Controller;

use App\Controller\UserController;
use App\DataFixtures\BandTestFixtures;
use App\DataFixtures\ConcertTestFixtures;
use App\DataFixtures\UserTestFixtures;
use App\Entity\Band;
use App\Entity\Concert;
use App\Controller\ConcertController;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class ConcertControllerIntegrationTest extends WebTestCase
{
    private static FakerGenerator $faker;
    protected static KernelBrowser $client;

    public function setUp(): void
    {
        self::$client = $this->createClient();
        $databaseTool = self::$client->getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([
            UserTestFixtures::class,
            BandTestFixtures::class,
            ConcertTestFixtures::class
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
        self::$client->request(Request::METHOD_GET, ConcertController::ROOT_PATH . '?band=testBand-1');
        $response = self::$client->getResponse();
        $concertList = json_decode($response->getContent(), true);
        $id = $concertList[0]['id'];
        self::$client->request(Request::METHOD_GET, ConcertController::ROOT_PATH . "/" . $id);
        $response = self::$client->getResponse();
        self::assertResponseIsSuccessful();
        self::assertJson($response->getContent());
        $receivedConcert = json_decode($response->getContent(), true);
        self::assertEquals($concertList[0], $receivedConcert);
    }

    public function testGetListIT(){
        self::$client->request(Request::METHOD_GET, ConcertController::ROOT_PATH . '?band=testBand-1');
        $response = self::$client->getResponse();
        self::assertResponseIsSuccessful();
        self::assertJson($response->getContent());
        $receivedConcertList = json_decode($response->getContent(), true);
        $index = 1;
        foreach ($receivedConcertList as $concert){
            self::assertArrayHasKey("name", $concert);
            if(!str_contains($concert["name"], "A Eliminar")) {
                self::assertMatchesRegularExpression('/\d$/', $concert['name']);
                self::assertLessThanOrEqual(count($receivedConcertList), explode(' ', $concert['name'])[1]);
            }
            self::assertArrayHasKey("id", $concert);
            self::assertArrayHasKey("color", $concert);
            self::assertArrayHasKey("state", $concert);
            self::assertArrayHasKey("date", $concert);
            self::assertIsArray($concert["date"]);
            self::assertArrayHasKey("address", $concert);
            self::assertArrayHasKey("modality", $concert);
            $index ++;
        }
    }

    public function testCreateIT()
    {
        $band = new Band();
        $band->setName('testBand-2');
        $concert = new Concert();
        $concert->setName(self::$faker->sentence(3))->setAddress(self::$faker->address())->setBand($band);
        $jsonArray = json_decode(json_encode($concert), true);
        $jsonArray['date'] = [
            '_date' => $jsonArray['date']['date'],
            '_timezone_type' => $jsonArray['date']['timezone_type'],
            '_timezone' => $jsonArray['date']['timezone']
        ];
        self::$client->request(
            Request::METHOD_POST,
            ConcertController::ROOT_PATH,
            content: json_encode($jsonArray)
        );
        $response = self::$client->getResponse();
        $receivedData = json_decode($response->getContent(),true);
        self::assertResponseIsSuccessful();
        self::assertJson($response->getContent());
        self::assertSame($concert->getName(), $receivedData["name"]);
        self::assertSame($concert->getAddress(), $receivedData["address"]);
    }

    public function testCreateIdentityDbErrorIT()
    {
        $band = new Band();
        $band->setName('testBand-2');
        $concert = new Concert();
        $concert->setName(self::$faker->sentence(3))->setAddress(self::$faker->address())->setBand($band);
        $jsonArray = json_decode(json_encode($concert), true);
        $jsonArray['date'] = [
            '_date' => $jsonArray['date']['date'],
            '_timezone_type' => $jsonArray['date']['timezone_type'],
            '_timezone' => $jsonArray['date']['timezone']
        ];
        self::$client->request(
            Request::METHOD_POST,
            ConcertController::ROOT_PATH,
            content: json_encode($jsonArray)
        );
        self::assertResponseIsSuccessful();
        self::$client->request(
            Request::METHOD_POST,
            ConcertController::ROOT_PATH,
            content: json_encode($jsonArray)
        );
        $response = self::$client->getResponse();
        $receivedData = json_decode($response->getContent(),true);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJson($response->getContent());
        self::assertArrayHasKey("message", $receivedData[0], "JSON hasn't got the right format");
        self::assertArrayHasKey("statusCode", $receivedData[0], "JSON hasn't got the right format");
    }

    public function testCreateWrongFormatIT()
    {
        $band = new Band();
        $band->setName('testBand-2');
        $concert = new Concert();
        $concert->setName(self::$faker->sentence(3))->setBand($band);
        self::$client->request(
            Request::METHOD_POST,
            ConcertController::ROOT_PATH,
            content: json_encode($concert)
        );
        $response = self::$client->getResponse();
        $receivedData = json_decode($response->getContent(),true);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJson($response->getContent());
        self::assertArrayHasKey("message", $receivedData[0], "JSON hasn't got the right format");
        self::assertArrayHasKey("statusCode", $receivedData[0], "JSON hasn't got the right format");
        $concert = new Concert();
        $concert->setName(self::$faker->sentence(3))->setBand($band);
        $jsonArray = json_decode(json_encode($concert), true);
        $jsonArray['name'] = '';
        $jsonArray['date'] = [
            '_date' => $jsonArray['date']['date'],
            '_timezone_type' => $jsonArray['date']['timezone_type'],
            '_timezone' => $jsonArray['date']['timezone']
        ];
        self::$client->request(
            Request::METHOD_POST,
            ConcertController::ROOT_PATH,
            content: json_encode($jsonArray)
        );
        $response = self::$client->getResponse();
        $receivedData = json_decode($response->getContent(),true);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJson($response->getContent());
        self::assertArrayHasKey("message", $receivedData[0], "JSON hasn't got the right format");
        self::assertArrayHasKey("statusCode", $receivedData[0], "JSON hasn't got the right format");
    }

    public function testDeleteIT(){
        self::$client->request(Request::METHOD_GET, ConcertController::ROOT_PATH . '?band=testBand-1');
        $response = self::$client->getResponse();
        $concertList = json_decode($response->getContent(), true);
        $i = 0;
        $concert = null;
        do{
            if(str_contains($concertList[$i]['name'], 'Eliminar')) {
                $concert = $concertList[$i];
            }
            $i ++;
        }while($i < count($concertList) && is_null($concert));
        $id = $concert['id'];
        self::$client->request(Request::METHOD_DELETE, ConcertController::ROOT_PATH . "/" . $id);
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::$client->request(Request::METHOD_GET, ConcertController::ROOT_PATH . "/" . $id);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testOptionsIT(){
        self::$client->request(
            Request::METHOD_OPTIONS,
            ConcertController::ROOT_PATH
        );
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $response = self::$client->getResponse();
        $headers = $response->headers->all();
        self::assertTrue($response->isEmpty());
        self::assertEmpty($response->getContent());
        self::assertArrayHasKey('access-control-allow-methods', $headers);
        self::assertArrayHasKey('access-control-allow-headers', $headers);
        self::$client->request(
            Request::METHOD_OPTIONS,
            ConcertController::ROOT_PATH . '/' . '{id}'
        );
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $response = self::$client->getResponse();
        $headers = $response->headers->all();
        self::assertTrue($response->isEmpty());
        self::assertEmpty($response->getContent());
        self::assertArrayHasKey('access-control-allow-methods', $headers);
        self::assertArrayHasKey('access-control-allow-headers', $headers);
    }


}
