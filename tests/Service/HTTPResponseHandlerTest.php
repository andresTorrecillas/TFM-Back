<?php

namespace App\Tests\Service;

use App\Service\HTTPErrorHandler;
use App\Service\HTTPResponseHandler;
use App\Service\JWTService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class HTTPResponseHandlerTest extends TestCase
{
    private HTTPErrorHandler $errorHandlerMock;
    private HTTPResponseHandler $responseHandler;

    protected function setUp(): void
    {
        $jwtMock = $this->createMock(JWTService::class);
        $stackMock = $this->createMock(RequestStack::class);
        $this->errorHandlerMock = $this->createMock(HTTPErrorHandler::class);
        $sessionMock = $this->createMock(Session::class);
        $sessionMock
            ->expects($this->any())
            ->method('get')
            ->willReturn('userName');
        $stackMock
            ->expects($this->any())
            ->method('getSession')
            ->willReturn($sessionMock);
        $jwtMock
            ->expects($this->any())
            ->method('generateTokenByUserName')
            ->willReturn('token');
        $this->responseHandler = new HTTPResponseHandler($stackMock, $jwtMock, $this->errorHandlerMock);
    }

    public function testGenerateResponse()
    {
        $this->errorHandlerMock
            ->expects($this->any())
            ->method('isCorrect')
            ->willReturn(true);
        $response = $this->responseHandler->generateResponse();
        self::assertTrue($response->isOk());
        self::assertEmpty($response->getContent());
        self::assertArrayHasKey('authorization', $response->headers->all());
        self::assertStringContainsString('Bearer', $response->headers->get('authorization'));
        $response = $this->responseHandler->generateResponse('Cuerpo', Response::HTTP_ACCEPTED);
        self::assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());
        self::assertJson($response->getContent());
    }

    public function testGenerateErrorResponse(){
        $this->errorHandlerMock
            ->expects($this->any())
            ->method('isCorrect')
            ->willReturn(false);
        $this->errorHandlerMock
            ->expects($this->any())
            ->method('getErrors')
            ->willReturn(['errors']);
        $this->errorHandlerMock
            ->expects($this->any())
            ->method('getErrorStatusCode')
            ->willReturn(Response::HTTP_BAD_REQUEST);
        $response = $this->responseHandler->generateResponse('Cuerpo', Response::HTTP_ACCEPTED);
        self::assertTrue($response->isClientError(), 'Response');
        self::assertJson($response->getContent(), 'Response');
        self::assertStringNotContainsString('Cuerpo', $response->getContent(), 'Response');
        $response = $this->responseHandler->generateLoginResponse('Cuerpo');
        self::assertTrue($response->isClientError(), 'LoginResponse');
        self::assertJson($response->getContent(), 'LoginResponse');
        self::assertStringNotContainsString('Cuerpo', $response->getContent(), 'LoginResponse');
        $response = $this->responseHandler->generateLogoutResponse();
        self::assertTrue($response->isOk(), 'LogoutResponse');
        self::assertEmpty($response->getContent(), 'LogoutResponse');
        $response = $this->responseHandler->generateRegisterResponse('Cuerpo');
        self::assertTrue($response->isClientError(), 'RegisterResponse');
        self::assertJson($response->getContent(), 'RegisterResponse');
        self::assertStringNotContainsString('Cuerpo', $response->getContent(), 'RegisterResponse');
        $response = $this->responseHandler->generateOptionsResponse();
        self::assertTrue($response->isClientError(), 'OptionsResponse');
        self::assertJson($response->getContent(), 'OptionsResponse');
        self::assertNotEmpty($response->getContent(), 'OptionsResponse');
    }

    public function testGenerateLogoutResponse()
    {
        $this->errorHandlerMock
            ->expects($this->any())
            ->method('isCorrect')
            ->willReturn(true);
        $response = $this->responseHandler->generateLogoutResponse();
        self::assertTrue($response->isOk());
        self::assertEmpty($response->getContent());
    }

    public function testGenerateLoginResponse()
    {
        $this->errorHandlerMock
            ->expects($this->any())
            ->method('isCorrect')
            ->willReturn(true);
        $response = $this->responseHandler->generateLoginResponse(['cuerpo']);
        self::assertTrue($response->isOk());
        self::assertJson($response->getContent());
    }

    public function testGenerateRegisterResponse()
    {
        $this->errorHandlerMock
            ->expects($this->any())
            ->method('isCorrect')
            ->willReturn(true);
        $response = $this->responseHandler->generateRegisterResponse(['cuerpo']);
        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        self::assertJson($response->getContent());
    }

    public function testGenerateOptionsResponse()
    {
        $this->errorHandlerMock
            ->expects($this->any())
            ->method('isCorrect')
            ->willReturn(true);
        $response = $this->responseHandler->generateOptionsResponse();
        self::assertTrue($response->isEmpty());
        self::assertEmpty($response->getContent());
        self::assertArrayHasKey('access-control-allow-methods', $response->headers->all());
        self::assertArrayHasKey('access-control-allow-headers', $response->headers->all());
    }
}
