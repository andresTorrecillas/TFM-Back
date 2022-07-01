<?php

namespace App\Service;

use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

class HttpError implements JsonSerializable
{
    private string $message;
    private ?string $details;
    private int $status;


    /**
     * @param string $message
     * @return HttpError
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param int $status
     * @return HttpError
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setDetails(?string $details): void
    {
        $this->details = $details;
    }

    public function jsonSerialize():array
    {
        $array = [
            "message" => $this->message,
            "statusCode" => $this->status
        ];
        if(isset($this->details) /*&& $_ENV["APP_ENV"] == "dev"*/){
            $array["details"] = $this->details;
        }
        return $array;
    }
}