<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\Response;

trait HTTPResponseHandler
{
    private array $errors;
    private int $primaryStatus = Response::HTTP_OK;
    private bool $correct = true;

    public function addError(int $status, string $message = ''): void
    {
        $httpError = new HttpError();
        $httpError->setMessage($message)->setStatus($status);
        if($status > Response::HTTP_BAD_REQUEST && $status < Response::HTTP_INTERNAL_SERVER_ERROR){
            $this->primaryStatus = Response::HTTP_BAD_REQUEST;
        } else if($this->correct){
            $this->primaryStatus = Response::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->correct = false;
        $this->errors[] = $httpError;
    }

    public function generateResponse(mixed $body = ''): Response|null
    {
        $message = empty($body)?$body:json_encode($body);
        if(!$this->correct){
            $message = json_encode($this->errors);
        }
        return new Response($message, $this->primaryStatus, ["Content-Type" => "application/json"]);
    }


}