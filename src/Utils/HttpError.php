<?php

namespace App\Utils;

use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

class HttpError implements JsonSerializable
{
    private string $message;
    private int $status;

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

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
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
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


    #[ArrayShape(["message" => "string", "status_code" => "int"])]
    public function jsonSerialize():array
    {
        return [
            "message" => $this->message,
            "status_code" => $this->status
        ];
    }
}