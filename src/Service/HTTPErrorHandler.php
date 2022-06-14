<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;

class HTTPErrorHandler
{
    private array $errors;
    private bool $correct;
    private int $errorStatus;

    public function __construct()
    {
        $this->errors = [];
        $this->correct = true;
        $this->errorStatus = Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function addError(string $message, int $status, ?string $details = null): void{
        $httpError = new HttpError();
        $httpError->setMessage($message)->setStatus($status)->setDetails($details);
        if($status >= Response::HTTP_BAD_REQUEST && $status < Response::HTTP_INTERNAL_SERVER_ERROR){
            $this->errorStatus = $status == Response::HTTP_NOT_FOUND ?
                Response::HTTP_NOT_FOUND :
                Response::HTTP_BAD_REQUEST;
        }
        $this->correct = false;
        $this->errors[] = $httpError;
    }

    public function isCorrect(): bool{
        return $this->correct;
    }

    public function getErrors(): array{
        return $this->errors;
    }

    public function getErrorStatusCode(): int{
        return $this->errorStatus;
    }

}