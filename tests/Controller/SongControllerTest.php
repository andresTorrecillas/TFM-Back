<?php

namespace App\Tests\Controller;

use App\Controller\SongController;
use App\Entity\Song;
use App\Repository\SongRepository;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function PHPUnit\Framework\assertEmpty;

class SongControllerTest extends TestCase
{
    private MockObject $SongRepositoryMock;
    private ManagerRegistry $mockedOrm;
    private static FakerGenerator $faker;

    public static function setUpBeforeClass(): void
    {
        self::$faker = FakerFactory::create('es_ES');
    }

    protected function setUp(): void
    {
        $this->SongRepositoryMock = $this->createMock(SongRepository::class);
        $this->mockedOrm = $this->createMock(ManagerRegistry::class);
        $this->mockedOrm->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->SongRepositoryMock);
    }

    public function testGetSong(){
        $song = new Song();
        $song->setTitle(self::$faker->sentence(3))->setLyrics(self::$faker->text());
        $songController = new SongController();
        $this->SongRepositoryMock->expects($this->any())
            ->method('find')
            ->willReturn($song);
        $response = $songController->get(1, $this->mockedOrm);
        $this->assertTrue($response->isOk(), "Failed status");
        $this->assertJson($response->getContent());
        $receivedData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey("title", $receivedData);
        $this->assertArrayHasKey("lyrics", $receivedData);
    }

    public function testGetNotFound(){
        $song = new Song();
        $song->setTitle(self::$faker->sentence(3))->setLyrics(self::$faker->text());
        $songController = new SongController();
        $this->SongRepositoryMock->expects($this->any())
            ->method('find')
            ->willReturn(null);
        $response = $songController->get(0, $this->mockedOrm);
        $this->assertTrue($response->isNotFound(), "Failed status \n" . $response);
    }

    public function testCreateSong()
    {
        $song = new Song();
        $song->setTitle(self::$faker->sentence(3))->setLyrics(self::$faker->text());
        $songController = new SongController();
        $this->SongRepositoryMock->expects($this->any())
            ->method('add');
        $response = $songController->create(new Request(content:json_encode($song)), $this->mockedOrm);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $receivedSong = json_decode($response->getContent(), true);
        $this->assertSame($song->getTitle(), $receivedSong['title']);
        $this->assertSame($song->getLyrics(), $receivedSong['lyrics']);
    }

    public function testCreateSongWithoutLyrics()
    {
        $song = new Song();
        $song->setTitle(self::$faker->sentence(3));
        $songController = new SongController();
        $this->SongRepositoryMock->expects($this->any())
            ->method('add');
        $response = $songController->create(new Request(content:json_encode($song)), $this->mockedOrm);

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $receivedSong = json_decode($response->getContent(), true);
        $this->assertArrayHasKey("title", $receivedSong);
        $this->assertSame($song->getTitle(), $receivedSong['title']);
    }

    public function testCreateSongInvalidCharacterInLyrics(){
        $song = new Song();
        $lyrics = self::$faker->text()."_";
        $song->setTitle(self::$faker->sentence(3))->setLyrics($lyrics);
        $songController = new SongController();
        $this->SongRepositoryMock->expects($this->any())
            ->method('add');
        $jsonSong = json_encode($song);
        $jsonSong = str_replace('"lyrics":""', "\"lyrics\":\"$lyrics\"", $jsonSong);
        $response = $songController->create(new Request(content:$jsonSong), $this->mockedOrm);

        $this->assertJson($response->getContent());
        $receivedData = json_decode($response->getContent(), true);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertCount(1, $receivedData);
        $this->assertArrayHasKey("message", $receivedData[0], "JSON hasn't got the right format");
        $this->assertArrayHasKey("status_code", $receivedData[0], "JSON hasn't got the right format");
        $this->assertEquals($response->getStatusCode(), $receivedData[0]["status_code"], 'HTTP status code doesn\'t match with JSON status code' );
    }

    public function testCreateSongWithoutTitle(){
        $song = new Song();
        $lyrics = self::$faker->text() . "_";
        $song->setLyrics($lyrics);
        $songController = new SongController();
        $this->SongRepositoryMock->expects($this->any())
            ->method('add');
        $response = $songController->create(new Request(content:json_encode($song)), $this->mockedOrm);

        $this->assertJson($response->getContent());
        $receivedData = json_decode($response->getContent(), true);
        echo $response->getContent();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertCount(1, $receivedData);
        $this->assertArrayHasKey("message", $receivedData[0], "JSON hasn't got the right format");
        $this->assertArrayHasKey("status_code", $receivedData[0], "JSON hasn't got the right format");
        $this->assertEquals($response->getStatusCode(), $receivedData[0]["status_code"], 'HTTP status code doesn\'t match with JSON status code' );
    }

    public function testGetList(){
        $song = new Song();
        $song->setTitle(self::$faker->sentence(3))->setLyrics(self::$faker->text());
        $songController = new SongController();
        $this->SongRepositoryMock->expects($this->any())
            ->method('findAll')
            ->willReturn([$song]);
        $response = $songController->getList($this->mockedOrm);
        $this->assertTrue($response->isOk(), "Failed status");
        $receivedData = json_decode($response->getContent(), true);
        foreach ($receivedData as $value) {
            $this->assertArrayHasKey("title", $value);
            $this->assertArrayHasKey("lyrics", $value);
        }
    }

    public function testDeleteSong(){
        $songController = new SongController();
        $this->SongRepositoryMock->expects($this->any())
            ->method("remove");
        $response = $songController->delete("Delete-1", $this->mockedOrm);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testPatch(){
        $song = new Song();
        $song->setTitle(self::$faker->sentence(3))->setLyrics(self::$faker->text());
        $songController = new SongController();
        $this->SongRepositoryMock->expects($this->any())
            ->method('find')
            ->willReturn($song);
        $this->SongRepositoryMock->expects($this->any())
            ->method('add');
        $requestBody = json_encode(["lyrics" => self::$faker->text()]);
        $response = $songController->patch(
            "Update-1",
            new Request(content: $requestBody),
            $this->mockedOrm
        );
        $this->assertTrue($response->isOk());
        $this->assertEmpty($response->getContent());
    }
}
