<?php

namespace App\Controller;

use App\Entity\Concert;
use App\Service\HTTPResponseHandler;
use App\Service\OrmService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(ConcertController::ROOT_PATH)
 */
class ConcertController extends AbstractController
{
    public const ROOT_PATH = "/api/concert";
    private HTTPResponseHandler $httpHandler;
    private OrmService $orm;

    public function __construct(HTTPResponseHandler $httpHandler, OrmService $orm)
    {
        $this->httpHandler = $httpHandler;
        $this->orm = $orm;
    }

    /**
     * @Route("", name="app_list_concerts", methods={"GET"})]
     */
    public function list(): Response
    {
        $concertList = $this->orm->findAll(Concert::class);
        return $this->httpHandler->generateResponse($concertList);
    }

    /**
     * @Route("/{id}", name="app_get_concert", methods={"GET"})
     */
    public function getConcert(string $id): Response
    {
        $concert = $this->orm->find($id, Concert::class);
        if(is_null($concert)){
            $this->httpHandler->addError(Response::HTTP_NOT_FOUND, "No se ha encontrado ningún concierto con el id indicado");
        }
        return $this->httpHandler->generateResponse($concert);
    }

    /**
     * @Route("", name="options_concert", methods={"OPTIONS"})
     * @Route("/{id}", name="options_id_concert", methods={"OPTIONS"})
     */
    public function optionsRequest(): Response
    {
        return $this->httpHandler->generateOptionsResponse();
    }
}
