<?php

namespace App\Controller;

use App\Entity\Band;
use App\Entity\Concert;
use App\Service\AuthService;
use App\Service\HTTPResponseHandler;
use App\Service\OrmService;
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
    private AuthService $authService;

    public function __construct(HTTPResponseHandler $httpHandler, OrmService $orm, AuthService $authService)
    {
        $this->httpHandler = $httpHandler;
        $this->orm = $orm;
        $this->authService = $authService;
    }

    /**
     * @Route("", name="app_list_concerts", methods={"GET"})]
     */
    public function list(Request $request): Response
    {
        $concertList = [];
        if($request->query->has('band')){
            $band = $request->query->get('band');
            if($this->authService->isBandMember($band)){
                $concertList = $this->getBandConcerts($band);
            } else{
                $this->httpHandler->addError(Response::HTTP_FORBIDDEN, "The band indicated is not one of yours");
            }

        } else{
            $this->httpHandler->addError(Response::HTTP_BAD_REQUEST, "There's no specified band");
        }
        return $this->httpHandler->generateResponse($concertList);
    }

    private function getBandConcerts(string $band_name): array
    {
        $band = $this->orm->findOneBy(['name' => $band_name], Band::class);
        $concertList = $band->getConcerts();
        return $concertList->isEmpty() ? [] : $concertList->getValues();
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
            if(!$this->isUnique($concert->getName())){
                $this->httpHandler->addError(Response::HTTP_BAD_REQUEST, "Ya existe un concierto con el nombre indicado");
            } else {
                $this->orm->persist($concert);
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
     * @Route("/{id}", name="update_concert", methods={"PATCH"})
     */
    public function patch(string $id, Request $request): Response
    {
        $concert = $this->orm->find($id, Concert::class);
        if(isset($concert)){
            $updatedConcert = $this->updateConcertFromRequestBody($request, $concert);
            if(isset($updatedConcert)){
                $this->orm->persist($concert);
            }
        } else{
            $this->httpHandler->addError(Response::HTTP_NOT_FOUND, "No existe un concierto con el id indicado");
        }
        return $this->httpHandler->generateResponse();
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
        if (isset($receivedConcert) && !empty($receivedConcert["name"]) && !empty($receivedConcert['band'])) {
            $concert = new Concert();
            if(!$concert->initFromArray($receivedConcert)) {
                $this->httpHandler->addError(Response::HTTP_BAD_REQUEST, "No se ha enviado un concierto con un formato adecuado");
                return null;
            }
            $band = $this->orm->findOneBy(['name' => $receivedConcert['band']], Band::class);
            if(isset($band)){
                $concert->setBand($band);
                return $concert;
            } else{
                $this->httpHandler->addError(Response::HTTP_BAD_REQUEST, "Alguna de las bandas indicadas no se encuentra incluida en el sistema");
                return null;
            }
        }
        $this->httpHandler->addError(Response::HTTP_BAD_REQUEST, "No se ha enviado una concierto con un formato adecuado");
        return null;
    }

    private function updateConcertFromRequestBody(Request $request, Concert $concert): Concert|null
    {
        $body = $request->getContent();
        $receivedConcert = json_decode($body, true);
        $bandStr = $receivedConcert['band'];
        unset($receivedConcert['band']);
        if(!$concert->initFromArray($receivedConcert)) {
            $this->httpHandler->addError(Response::HTTP_BAD_REQUEST, "No se ha enviado un concierto con un formato adecuado");
        }
        $band = $this->orm->findOneBy(['name' => $bandStr], Band::class);
        if(isset($band)){
            $concert->setBand($band);
            return $concert;
        } else{
            $this->httpHandler->addError(Response::HTTP_BAD_REQUEST, "Alguna de las bandas indicadas no se encuentra incluida en el sistema");
            return null;
        }
    }
}
