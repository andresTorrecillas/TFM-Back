<?php

namespace App\Utils;

use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

class HttpError implements JsonSerializable
{
    private string $message;
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


    #[ArrayShape(["message" => "string", "statusCode" => "int"])]
    public function jsonSerialize():array
    {
        return [
            "message" => $this->message,
            "statusCode" => $this->status
        ];
    }
}