<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserTestFixtures;
use App\Controller\UserController;
use App\Service\Base64Service;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class UserControllerIntegrationTest extends WebTestCase
{
    protected static KernelBrowser $client;

    public function setUp(): void
    {
        self::$client = $this->createClient();
        $databaseTool = self::$client->getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures([
            UserTestFixtures::class
        ]);
        $session = new Session(new MockFileSessionStorage());
        self::$client->getContainer()->set('session', $session);
    }

    public function testLoginIT()
    {
        $password = Base64Service::encode('test_psw');
        $userName = 'test';
        $user = [
            'userName' => $userName,
            'password' => $password
        ];
        self::$client->request(
            Request::METHOD_POST,
            UserController::ROOT_PATH . '/login',
            content: json_encode($user)
        );
        $response = self::$client->getResponse();
        $receivedData = json_decode($response->getContent(), true);
        self::assertResponseIsSuccessful();
        self::assertJson($response->getContent());
        self::assertArrayHasKey('token', $receivedData);
        self::assertMatchesRegularExpression('/^[A-Za-z\d\-_]+\.[A-Za-z\d\-_]+\.[A-Za-z\d\-_]+$/', $receivedData['token']);
        self::assertArrayHasKey('expirationDate', $receivedData);
        self::assertArrayHasKey('user', $receivedData);
        self::assertIsArray($receivedData['user']);
        self::assertArrayHasKey('userName', $receivedData['user']);
        self::assertEquals($user['userName'], $receivedData['user']['userName']);
    }

    public function testLoginIncorrectCredentialsIT()
    {
        $password = Base64Service::encode('wrong');
        $userName = 'test';
        $user = [
            'userName' => $userName,
            'password' => $password
        ];
        self::$client->request(
            Request::METHOD_POST,
            UserController::ROOT_PATH . '/login',
            content: json_encode($user)
        );
        $response = self::$client->getResponse();
        $receivedData = json_decode($response->getContent(), true);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJson($response->getContent());
        self::assertArrayHasKey("message", $receivedData[0], "JSON hasn't got the right format");
        self::assertArrayHasKey("statusCode", $receivedData[0], "JSON hasn't got the right format");
        self::assertEquals(Response::HTTP_UNAUTHORIZED, $receivedData[0]['statusCode']);
    }

    public function testLoginBadFormattedIT()
    {
        $userName = 'test';
        $user = [
            'userName' => $userName
        ];
        self::$client->request(
            Request::METHOD_POST,
            UserController::ROOT_PATH . '/login',
            content: json_encode($user)
        );
        $response = self::$client->getResponse();
        $receivedData = json_decode($response->getContent(), true);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJson($response->getContent());
        self::assertArrayHasKey("message", $receivedData[0], "JSON hasn't got the right format");
        self::assertArrayHasKey("statusCode", $receivedData[0], "JSON hasn't got the right format");
    }

    public function testLogoutIT(){
        self::$client->request(
            Request::METHOD_GET,
            UserController::ROOT_PATH . '/logout'
        );
        self::assertResponseIsSuccessful();
    }

    public function testRegisterIT()
    {
        $password = 'MTIzNDU2'; //base64 url encoded
        $userName = 'New BandUser';
        $user = [
            "password" => $password,
            "user" =>  [ "userName" => $userName ]
        ];
        self::$client->request(
            Request::METHOD_POST,
            UserController::ROOT_PATH . '/register',
            content: json_encode($user)
        );
        $response = self::$client->getResponse();
        $receivedData = json_decode($response->getContent(), true);
        self::assertResponseIsSuccessful();
        self::assertJson($response->getContent());
        self::assertSame($userName, $receivedData["userName"]);
    }

    public function testRegisterBadFormattedIT()
    {
        $password = 'MTIzNDU2'; //base64 url encoded
        $userName = 'New BandUser';
        $user = [
            "user" =>  [ "userName" => $userName, "password" => $password ]
        ];
        self::$client->request(
            Request::METHOD_POST,
            UserController::ROOT_PATH . '/register',
            content: json_encode($user)
        );
        $response = self::$client->getResponse();
        $receivedData = json_decode($response->getContent(), true);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertJson($response->getContent());
        self::assertArrayHasKey("message", $receivedData[0], "JSON hasn't got the right format");
        self::assertArrayHasKey("statusCode", $receivedData[0], "JSON hasn't got the right format");
    }

    public function testOptionsIT()
    {
        self::$client->request(
            Request::METHOD_OPTIONS,
            UserController::ROOT_PATH . '/register'
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
            UserController::ROOT_PATH . '/login'
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
