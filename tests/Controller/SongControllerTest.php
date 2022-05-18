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
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    public function testCreateSong()
    {
        $song = new Song();
        $song->setTitle(self::$faker->sentence(3))->setLyrics(self::$faker->text());
        $songController = new SongController();
        $this->SongRepositoryMock->expects($this->any())
            ->method('add');
        $response = $songController->createSong(new Request(content:json_encode($song)), $this->mockedOrm);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $receivedSong = json_decode($response->getContent(), true);
        echo $response->getContent();
        $this->assertSame($song->getTitle(), $receivedSong['title']);
        $this->assertSame($song->getLyrics(), $receivedSong['lyrics']);
    }
}
