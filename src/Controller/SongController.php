<?php

namespace App\Controller;

use App\Entity\Song;
use App\Utils\HTTPResponseHandler;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(SongController::ROOT_PATH)
 */
class SongController extends AbstractController
{
    use HTTPResponseHandler;
    public const ROOT_PATH = "/api/song";

    /**
     * @Route("/{id}", name="app_song", methods={"GET"})
     */
    public function index(int $id, ManagerRegistry $orm): Response
    {
        $song = $orm->getRepository(Song::class)
            ->find($id);
        if(is_null($song)){
            $this->addError(Response::HTTP_NOT_FOUND, "No se ha encontrado ninguna canción con el id indicado");
        }
        return $this->generateResponse($song);
    }

    /**
     * @Route("", name="new_song", methods={"POST"})
     */
    public function createSong(Request $request, ManagerRegistry $orm): Response
    {
        $song = $this->getSongFromRequestBody($request);
        if (isset($song)) {
            try {
                if(!$this->isUnique($song->getTitle(),$orm)){
                    $this->addError(Response::HTTP_BAD_REQUEST, "Ya existe una canción con el título indicado");
                } else {
                    $this->persist($song, $orm);
                }
            } catch (Exception $exception){
                $this->addError(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
            }
        }
        return $this->generateResponse($song);
    }

    private function isUnique(string $title, ManagerRegistry $orm): bool|null
    {
        $db = $orm->getRepository(Song::class);
        return is_null($db->findOneBy(['title' => $title]));
    }

    private function getSongFromRequestBody(Request $request): Song|null
    {
        $body = $request->getContent();
        $receivedSong = json_decode($body, true);
        if (isset($receivedSong) && !empty($receivedSong["title"])) {
            $song = new Song();
            $song->setTitle($receivedSong["title"]);
            if (!empty($receivedSong["lyrics"])) {
                if(!$song->setLyrics($receivedSong["lyrics"])){
                    $this->addError(Response::HTTP_BAD_REQUEST, "La letra contiene caracteres no admitidos");
                    return null;
                }
            }
            return $song;
        }
        $this->addError(Response::HTTP_BAD_REQUEST, "No se ha enviado una canción con un formato adecuado");
        return null;
    }

    private function persist(Song $song, ManagerRegistry $orm): void
    {
        try {
            $db = $orm->getRepository(Song::class);
            $db->add($song, true);
        } catch (Exception){
            $this->addError(Response::HTTP_INTERNAL_SERVER_ERROR, "No se ha podido guardar la canción por un error en el servidor");
        }

    }
}
