<?php

namespace App\Tests\Service;

use App\Service\Base64Service;
use PHPUnit\Framework\TestCase;

class Base64ServiceTest extends TestCase
{
    private const PLAIN_TEXT_WORD = 'Correcto~?ÿ_';
    private const BASE_ENCODED_WORD = 'Q29ycmVjdG9+P8O/Xw==';
    private const URL_ENCODED_WORD = 'Q29ycmVjdG9-P8O_Xw';

    public function testEncode()
    {
        self::assertMatchesRegularExpression('/^[A-Za-z\d\/+]+={0,3}$/', Base64Service::encode(self::PLAIN_TEXT_WORD));
        self::assertEquals(self::BASE_ENCODED_WORD, Base64Service::encode(self::PLAIN_TEXT_WORD));
    }

    public function testDecode()
    {
        self::assertEquals(self::PLAIN_TEXT_WORD, Base64Service::decode(self::BASE_ENCODED_WORD));
    }

    public function testUrl_encode()
    {
        self::assertMatchesRegularExpression('/^[A-Za-z\d\-_]+$/', Base64Service::url_encode(self::PLAIN_TEXT_WORD));
        self::assertEquals(self::URL_ENCODED_WORD, Base64Service::url_encode(self::PLAIN_TEXT_WORD));
    }

    public function testUrl_decode()
    {
        self::assertEquals(self::PLAIN_TEXT_WORD, Base64Service::url_decode(self::URL_ENCODED_WORD));
    }


}
