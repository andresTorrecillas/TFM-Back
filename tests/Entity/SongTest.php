<?php

namespace App\Tests\Entity;

use App\Entity\Song;
use Faker\Generator as FakerGenerator;
use Faker\Factory as FakerFactory;
use PHPUnit\Framework\TestCase;

class SongTest extends TestCase
{
    protected static FakerGenerator $faker;

    public static function setUpBeforeClass(): void
    {
        self::$faker = FakerFactory::create('es_ES');
    }

    public function testSetLyrics()
    {
        $song = new Song();
        $lyrics = self::$faker->text();
        preg_replace("[^\da-zA-ZÁ-ÿ\040\-\n.,\'?!]", " ", $lyrics);
        $song->setLyrics($lyrics);
        $this->assertSame($lyrics, $song->getLyrics());
    }

    public function testSetLyricsWithInvalidChars()
    {
        $song = new Song();
        $this->assertFalse($song->setLyrics(self::$faker->text()."<>"));
        $this->assertSame("", $song->getLyrics());
    }

    public function testJsonSerialize()
    {
        $song = new Song();
        $song->setTitle(self::$faker->sentence(3))->setLyrics(self::$faker->text());
        $jsonArray = $song->jsonSerialize();
        $this->assertArrayHasKey("id", $jsonArray);
        $this->assertArrayHasKey("title", $jsonArray);
        $this->assertArrayHasKey("lyrics", $jsonArray);
    }
}
