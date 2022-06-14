<?php

namespace App\Service;

use phpDocumentor\Reflection\Types\This;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class HTTPResponseHandler
{
    private int $primaryStatus = Response::HTTP_OK;
    private array $headers = [];
    private RequestStack $requestStack;
    private HTTPErrorHandler $httpErrorHandler;
    private JWTService $jwtService;

    public function __construct(RequestStack $requestStack, JWTService $jwtService, HTTPErrorHandler $httpErrorH)
    {
        $this->requestStack = $requestStack;
        $this->httpErrorHandler = $httpErrorH;
        $this->jwtService = $jwtService;
    }

    public function addHeaders(array $headers):void {
        $this->headers = array_merge($this->headers, $headers);
    }

    public function addError(int $status, string $message = ''): void
    {
        $this->httpErrorHandler->addError($message, $status);
    }

    public function generateResponse(mixed $body = '', int $correctStatus = Response::HTTP_OK): Response
    {
        if($this->httpErrorHandler->isCorrect()) {
            $token = $this->generateAuthToken();
            if (isset($token)) {
                $this->addHeaders(["authorization" => "Bearer " . $token]);
            }
        }
        if(!$this->httpErrorHandler->isCorrect()){
            $responseBody = $this->httpErrorHandler->getErrors();
            $this->primaryStatus = $this->httpErrorHandler->getErrorStatusCode();
        } else{
            $responseBody = $body;
            $this->primaryStatus = $correctStatus;
        }

        return $this->generateBasicResponse($responseBody, $this->primaryStatus);
    }

    public function generateOptionsResponse(): Response
    {
        if(!$this->httpErrorHandler->isCorrect()){
            $responseBody = $this->httpErrorHandler->getErrors();
            $this->primaryStatus = $this->httpErrorHandler->getErrorStatusCode();
        } else{
            $responseBody = "";
            $this->primaryStatus = Response::HTTP_NO_CONTENT;
        }
        return $this->generateBasicResponse($responseBody, $this->primaryStatus, true);
    }

    public function generateLoginResponse(mixed $body): Response {
        if(!$this->httpErrorHandler->isCorrect()){
            $responseBody = $this->httpErrorHandler->getErrors();
            $this->primaryStatus = $this->httpErrorHandler->getErrorStatusCode();
        } else{
            $responseBody = $body;
        }
        return $this->generateBasicResponse($responseBody, $this->primaryStatus);
    }

    public function generateRegisterResponse(mixed $body): Response {
        if(!$this->httpErrorHandler->isCorrect()){
            $responseBody = $this->httpErrorHandler->getErrors();
            $this->primaryStatus = $this->httpErrorHandler->getErrorStatusCode();
        } else{
            $responseBody = $body;
            $this->primaryStatus = Response::HTTP_CREATED;
        }
        return $this->generateBasicResponse($responseBody, $this->primaryStatus);
    }

    public function generateLogoutResponse(): Response
    {
        return $this->generateBasicResponse("", Response::HTTP_OK);
    }

    private function generateBasicResponse(mixed $body, int $status, bool $options = false): Response {
        $message = empty($body)?$body:json_encode($body);
        $headers = $this->generateHeaders($options);
        return new Response($message, $status, $headers);
    }

    private function generateHeaders(bool $options = false): array{
        $headers = $this->headers;
        if($options){
            $headers = [
                "Access-Control-Allow-Methods" => "POST, GET, DELETE, PATCH",
                "Access-Control-Allow-Headers" => "content-type, authorization, withCredentials"
            ];
        }
        return $headers;
    }

    private function generateAuthToken(): string|null{
        $session = $this->requestStack->getSession();
        $userName = $session->get("user");
        if(isset($userName)){
            return $this->jwtService->generateTokenByUserName($userName);
        } else{
            $this->httpErrorHandler
                ->addError(
                    "El usuario no está correctamente autenticado",
                    Response::HTTP_FORBIDDEN
                );
            return null;
        }


    }

}