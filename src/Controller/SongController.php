<?php

namespace App\Controller;

use App\Entity\Band;
use App\Entity\Song;
use App\Service\AuthService;
use App\Service\HTTPResponseHandler;
use App\Service\OrmService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(SongController::ROOT_PATH)
 */
class SongController extends AbstractController
{
    private HTTPResponseHandler $httpHandler;
    public const ROOT_PATH = "/api/song";
    private OrmService $orm;
    private AuthService $authService;

    public function __construct(HTTPResponseHandler $httpHandler, OrmService $orm, AuthService $authService)
    {
        $this->httpHandler = $httpHandler;
        $this->orm = $orm;
        $this->authService = $authService;
    }

    /**
     * @Route("/{id}", name="app_song", methods={"GET"})
     */
    public function get(string $id): Response
    {
        $song = $this->orm->find($id, Song::class);
        if(is_null($song)){
            $this->httpHandler->addError(Response::HTTP_NOT_FOUND, "No se ha encontrado ninguna canción con el id indicado");
        }
        return $this->httpHandler->generateResponse($song);
    }

    /**
     * @Route("", name="new_song", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $song = $this->getSongFromRequestBody($request);
        if (isset($song)) {
            if(!$this->isUnique($song->getTitle())){
                $this->httpHandler->addError(Response::HTTP_BAD_REQUEST, "Ya existe una canción con el título indicado");
            } else {
                $this->orm->persist($song);
            }
        }
        return $this->httpHandler->generateResponse($song, Response::HTTP_CREATED);
    }

    /**
     * @Route("", name="list_songs", methods={"GET"})
     */
    public function getList(Request $request): Response
    {
        $songList = [];
        if($request->query->has('band')){
            $band = $request->query->get('band');
            if($this->authService->isBandMember($band)){
                $songList = $this->getBandSongs($band);
            } else{
                $this->httpHandler->addError(Response::HTTP_FORBIDDEN, "The band indicated is not one of yours");
            }

        } else{
            $this->httpHandler->addError(Response::HTTP_BAD_REQUEST, "There's no specified band");
        }
        return $this->httpHandler->generateResponse($songList);
    }

    private function getBandSongs(string $band_name): array
    {
        $band = $this->orm->findOneBy(['name'=>$band_name], Band::class);
        $songList = $band->getSongs();
        return $songList->isEmpty() ? [] : $songList->getValues();
    }

    /**
     * @Route("/{id}", name="delete_song", methods={"DELETE"})
     */
    public function delete(string $id): Response
    {
        $song = $this->orm->find($id, Song::class);
        if(isset($song)){
            $this->orm->remove($song);
        }
        return $this->httpHandler->generateResponse(correctStatus: Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}", name="update_song", methods={"PATCH"})
     */
    public function patch(string $id, Request $request): Response
    {
        $song = $this->orm->find($id, Song::class);
        if(!isset($song)){
            $this->httpHandler->addError(Response::HTTP_NOT_FOUND, "No existe una canción con el id indicado");
        } else{
            $body = $request->getContent();
            $receivedSong = json_decode($body, true);
            $song
                ->setTitle($receivedSong["title"]??$song->getTitle())
                ->setLyrics($receivedSong["lyrics"]??$song->getLyrics());
            $this->orm->persist($song);
        }

        return $this->httpHandler->generateResponse();
    }

    /**
     * @Route("", name="options_songs", methods={"OPTIONS"})
     * @Route("/{id}", name="options_id_songs", methods={"OPTIONS"})
     * @Route("/band/{band_name}", name="options_band_songs", methods={"OPTIONS"})
     */
    public function optionsRequest(): Response{
        return $this->httpHandler->generateOptionsResponse();
    }

    private function isUnique(string $title): bool|null
    {
        return is_null($this->orm->findOneBy(['title' => $title], Song::class));
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
                    $this->httpHandler->addError(Response::HTTP_BAD_REQUEST, "La letra contiene caracteres no admitidos");
                    return null;
                }
            }
            return $song;
        }
        $this->httpHandler->addError(Response::HTTP_BAD_REQUEST, "No se ha enviado una canción con un formato adecuado");
        return null;
    }
}
