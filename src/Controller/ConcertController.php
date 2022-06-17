<?php

namespace App\Controller;

use App\Entity\Concert;
use App\Service\HTTPResponseHandler;
use App\Service\OrmService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function get(string $id): Response
    {
        $concert = $this->orm->find($id, Concert::class);
        if(is_null($concert)){
            $this->httpHandler->addError(Response::HTTP_NOT_FOUND, "No se ha encontrado ningún concierto con el id indicado");
        }
        return $this->httpHandler->generateResponse($concert);
    }

    /**
     * @Route("", name="app_create_concert", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $concert = $this->getConcertFromRequestBody($request);
        if (isset($concert)) {
            try {
                if(!$this->isUnique($concert->getName())){
                    $this->httpHandler->addError(Response::HTTP_BAD_REQUEST, "Ya existe un concierto con el nombre indicado");
                } else {
                    $this->orm->persist($concert);
                }
            } catch (Exception $exception){
                $this->httpHandler->addError(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getMessage());
            }
        }
        return $this->httpHandler->generateResponse($concert, Response::HTTP_CREATED);
    }

    /**
     * @Route("/{id}", name="delete_concert", methods={"DELETE"})
     */
    public function delete(string $id): Response
    {
        $concert = $this->orm->find($id, Concert::class);
        if(isset($concert)){
            $this->orm->remove($concert);
        }
        return $this->httpHandler->generateResponse(correctStatus: Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("", name="options_concert", methods={"OPTIONS"})
     * @Route("/{id}", name="options_id_concert", methods={"OPTIONS"})
     */
    public function optionsRequest(): Response
    {
        return $this->httpHandler->generateOptionsResponse();
    }

    private function isUnique(string $name): bool|null
    {
        return is_null($this->orm->findOneBy(['name' => $name], Concert::class));
    }

    private function getConcertFromRequestBody(Request $request): Concert|null
    {
        $body = $request->getContent();
        $receivedConcert = json_decode($body, true);
        if (isset($receivedConcert) && !empty($receivedConcert["name"])) {
            $concert = new Concert();
            if(!$concert->initFromArray($receivedConcert)) {
                $this->httpHandler->addError(Response::HTTP_BAD_REQUEST, "No se ha enviado un concierto con un formato adecuado");
                echo json_encode($receivedConcert);
                return null;
            }
            return $concert;
        }
        $this->httpHandler->addError(Response::HTTP_BAD_REQUEST, "No se ha enviado una concierto con un formato adecuado");
        return null;
    }
}
