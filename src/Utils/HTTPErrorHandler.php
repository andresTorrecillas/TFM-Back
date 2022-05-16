<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\Response;

trait HTTPErrorHandler
{
    private array $errors;
    private int $primaryError = Response::HTTP_INTERNAL_SERVER_ERROR;

    public function addErrorMessage(string $message, int $status): void
    {
        $httpError = new HttpError();
        $httpError->setMessage($message)->setStatus($status);
        if($status > Response::HTTP_BAD_REQUEST && $status < Response::HTTP_INTERNAL_SERVER_ERROR){
            $this->primaryError = Response::HTTP_BAD_REQUEST;
        }
        $this->errors[] = $httpError;
    }

    public function generateErrorResponse(): Response|null
    {
        if(empty($this->errors)){
            return null;
        }
        return new Response(json_encode($this->errors), $this->primaryError, ["Content-Type" => "application/json"]);
    }


}