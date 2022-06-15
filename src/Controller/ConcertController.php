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
     * @Route("", name="app_concert")]
     */
    public function list(): Response
    {
        $concertList = $this->orm->findAll(Concert::class);
        return $this->httpHandler->generateResponse($concertList);
    }
}
