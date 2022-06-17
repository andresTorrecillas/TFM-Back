<?php

namespace App\Tests\Entity;

use App\Entity\Concert;
use PHPUnit\Framework\TestCase;

class ConcertTest extends TestCase
{

    public function testInitFromArray()
    {
        $array = [
            "address" => "Garc\u00eda Luna, 13, 28002 Madrid",
            "color" => "#fb0e0e",
            "state" => "Cerrado",
            "date" => [
                "_date" => "2022-09-10T18:00:00+0000",
                "_timezone_type" => 3,
                "_timezone" => "Europe/Madrid"
            ],
            "id" => "",
            "modality" => "Ac\u00fastico",
            "name" => "Houdini"
        ];
        $concert = new Concert();
        self::assertTrue($concert->initFromArray($array));
    }

    public function testInitFromArrayAlternativeDateFormat()
    {
        $array = [
            "address" => "Garc\u00eda Luna, 13, 28002 Madrid",
            "color" => "#fb0e0e",
            "state" => "Cerrado",
            "date" => [
                "_date" => "2022-09-10T18:00:00.000Z",
                "_timezone_type" => 3,
                "_timezone" => "Europe\/Madrid"
            ],
            "id" => "",
            "modality" => "Ac\u00fastico",
            "name" => "Houdini"
        ];
        $concert = new Concert();
        self::assertTrue($concert->initFromArray($array));
    }

    public function testInitFromArrayNameNotSet()
    {
        $array = [
            "address" => "Garc\u00eda Luna, 13, 28002 Madrid",
            "color" => "#fb0e0e",
            "state" => "Cerrado",
            "date" => [
                "_date" => "2022-09-10T18:00:00.000Z",
                "_timezone_type" => 3,
                "_timezone" => "Europe\/Madrid"
            ],
            "id" => "",
            "modality" => "Ac\u00fastico",
            "name" => ""
        ];
        $concert = new Concert();
        self::assertFalse($concert->initFromArray($array));
    }
}
