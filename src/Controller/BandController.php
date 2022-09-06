<?php

namespace App\Controller;

use App\Service\AuthService;
use App\Service\HTTPResponseHandler;
use App\Service\OrmService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(BandController::ROOT_PATH)
 */
class BandController extends AbstractController
{
    public const ROOT_PATH = "/api/band";
    private HTTPResponseHandler $httpHandler;
    private AuthService $authService;
    private OrmService $orm;

    public function __construct(OrmService $orm, HTTPResponseHandler $httpHandler, AuthService $authService)
    {
        $this->httpHandler = $httpHandler;
        $this->orm = $orm;
        $this->authService = $authService;
    }

    /**
     * @Route("", name="band_list", methods={"GET"})
     */
    public function list(): Response
    {
        $bands = $this->authService->getUser()->getBandNamesList();
        return $this->httpHandler->generateResponse($bands);
    }

    /**
     * @Route("/summary", name="band_summary", methods={"GET"})
     */
    public function summary(): Response
    {
        $summaryInfo = [];
        $bandNames = $this->authService->getUser()->getBandNamesList();
        $bands = $this->orm->findBandsByName($bandNames);
        foreach ($bands as $band){
            $songs = $band->getSongs()->toArray();
            $concerts = $band->getConcerts()->toArray();
            $bandInfo["songs"] = array_map(function($item){
                return ["id" => $item->getId(), "title" => $item->getTitle()];
                }, $songs);
            $bandInfo["concerts"] = array_map(function($item){
                return ["id" => $item->getId(), "name" => $item->getName()];
                }, $concerts);
            $summaryInfo[$band->getName()] = $bandInfo;
        }
        return $this->httpHandler->generateResponse($summaryInfo);
    }

    /**
     * @Route("", name="options_list", methods={"OPTIONS"})
     * @Route("/summary", name="options_summary", methods={"OPTIONS"})
     */
    public function optionsRequest(): Response{
        return $this->httpHandler->generateOptionsResponse();
    }
}
