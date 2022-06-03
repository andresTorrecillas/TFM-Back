<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\Response;

define("METHOD_GET", "GET");
define("METHOD_POST", "POST");
define("METHOD_DELETE", "DELETE");
define("METHOD_OPTIONS", "OPTIONS");

trait HTTPResponseHandler
{
    private array $errors;
    private int $primaryStatus = Response::HTTP_OK;
    private bool $correct = true;

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

    public function generateResponse(mixed $body = '', string $method = METHOD_GET, int $correctStatus = Response::HTTP_OK): Response|null
    {
        if(!$this->correct){
            $message = json_encode($this->errors);
        } else{
            $message = empty($body)?$body:json_encode($body);
            $this->primaryStatus = $correctStatus;
        }
        if($method == METHOD_OPTIONS && $this->primaryStatus < Response::HTTP_MULTIPLE_CHOICES){
            $this->primaryStatus = Response::HTTP_NO_CONTENT;
        }
        $headers = $this->generateHeaders($method);
        return new Response($message, $this->primaryStatus, $headers);
    }

    private function generateHeaders(string $method): array{
        $headers = [];
        if($method == METHOD_OPTIONS){
            $headers = [
                "Access-Control-Allow-Methods" => "POST, GET, DELETE",
                "Access-Control-Allow-Headers" => "content-type"
            ];
        }
        return $headers;
    }

}