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
     * @Route("", name="options_list", methods={"OPTIONS"})
     */
    public function optionsRequest(): Response{
        return $this->httpHandler->generateOptionsResponse();
    }
}
