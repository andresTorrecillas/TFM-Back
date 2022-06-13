<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class HTTPResponseHandler
{
    private array $errors;
    private int $primaryStatus = Response::HTTP_OK;
    private bool $correct = true;
    private array $headers = [];
    private JWTService $jwtService;
    private RequestStack $requestStack;

    public function __construct(JWTService $jwtService, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->jwtService = $jwtService;
    }

    public function addHeaders(array $headers):void {
        $this->headers = array_merge($this->headers, $headers);
    }

    public function addError(int $status, string $message = ''): void
    {
        $httpError = new HttpError();
        $httpError->setMessage($message)->setStatus($status);
        if($status >= Response::HTTP_BAD_REQUEST && $status < Response::HTTP_INTERNAL_SERVER_ERROR){
            $this->primaryStatus = $status == Response::HTTP_NOT_FOUND ?
                Response::HTTP_NOT_FOUND :
                Response::HTTP_BAD_REQUEST;
        } else if($this->correct){
            $this->primaryStatus = Response::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->correct = false;
        $this->errors[] = $httpError;
    }

    public function generateResponse(mixed $body = '', int $correctStatus = Response::HTTP_OK): Response
    {
        if(!$this->correct){
            $responseBody = $this->errors;
        } else{
            $responseBody = $body;
            $this->primaryStatus = $correctStatus;
        }
        $token = $this->generateAuthToken();
        if(isset($token)){
            $this->addHeaders(["authorization" => "Bearer " . $token]);
        }
        return $this->generateBasicResponse($responseBody, $this->primaryStatus);
    }

    public function generateOptionsResponse(): Response
    {
        if(!$this->correct){
            $responseBody = $this->errors;
        } else{
            $responseBody = "";
            $this->primaryStatus = Response::HTTP_NO_CONTENT;
        }
        return $this->generateBasicResponse($responseBody, $this->primaryStatus, true);
    }

    public function generateLoginResponse(mixed $body): Response {
        if(!$this->correct){
            $responseBody = $this->errors;
        } else{
            $responseBody = $body;
        }
        return $this->generateBasicResponse($responseBody, $this->primaryStatus);
    }

    public function generateRegisterResponse(mixed $body): Response {
        if(!$this->correct){
            $responseBody = $this->errors;
        } else{
            $responseBody = $body;
            $this->primaryStatus = Response::HTTP_CREATED;
        }
        return $this->generateBasicResponse($responseBody, $this->primaryStatus);
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
                "Access-Control-Allow-Headers" => "content-type, authorization"
            ];
        }
        return $headers;
    }

    private function generateAuthToken(): string|null{
        $session = $this->requestStack->getSession();
        $userName = $session->get("user");
        if(isset($userName)){
            return $this->jwtService->generateTokenByUserName($userName);
        }
        return null;
    }

}