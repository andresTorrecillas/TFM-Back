<?php

namespace App\Controller;

use App\Entity\Song;
use App\Utils\HTTPErrorHandler;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SongController extends AbstractController
{
    use HTTPErrorHandler;
    /**
     * @Route("/song", name="app_song", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SongController.php',
        ]);
    }

    /**
     * @Route("/new/song", name="new_song", methods={"POST"})
     */
    public function createSong(Request $request, ManagerRegistry $orm): Response
    {
        $song = $this->getSongFromRequestBody($request);
        if (isset($song)) {
            if(!$this->isUnique($song->getTitle(),$orm)){
                return new Response("Ya existe una canción con el título indicado",Response::HTTP_BAD_REQUEST);
            }
            $this->persist($song, $orm);
        }
        return $this->generateErrorResponse()??$this->json($song);
    }

    private function isUnique(string $title, ManagerRegistry $orm): bool
    {
        $db = $orm->getRepository(Song::class);
        return is_null($db->findOneBy(['title' => $title]));
    }

    private function getSongFromRequestBody(Request $request): Song|null
    {
        $body = $request->getContent();
        $receivedSong = json_decode($body, true);
        if (isset($receivedSong) && isset($receivedSong["title"])) {
            $song = new Song();
            $song->setTitle($receivedSong["title"]);
            if (isset($receivedSong["lyrics"])) {
                if(!$song->setLyrics($receivedSong["lyrics"])){
                    echo "La letra contiene caracteres no admitidos\n";
                    return null;
                }
            }
            return $song;
        }
        echo "No se ha enviado una canción con un formato adecuado";
        return null;
    }

    private function persist(Song $song, ManagerRegistry $orm): void
    {
        $db = $orm->getRepository(Song::class);
        $db->add($song, true);
    }
}
