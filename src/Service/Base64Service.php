<?php

namespace App\Service;

class Base64Service
{
    public static function encode($data): string
    {
        return base64_encode($data);
    }

    public static function decode($data): string
    {
        return base64_decode($data);
    }

    public static function url_encode($data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function url_decode($data): string
    {
        return base64_decode(str_replace(array('-', '_'), array('+', '/'), $data));
    }
}