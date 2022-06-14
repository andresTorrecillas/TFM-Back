<?php

namespace App\EventListeners;

use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class EventListeners implements EventSubscriberInterface
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($event->isMainRequest() && isset($_ENV["CORS_ORIGIN"])) {
            $response = $event->getResponse();
            $response->headers->set("Access-Control-Allow-Origin", $_ENV["CORS_ORIGIN"]);
            $response->headers->set("Access-Control-Allow-Credentials", "true");
            if($response->getStatusCode() !== Response::HTTP_NO_CONTENT){
                $response->headers->set("Content-Type", "application/json");
            }
        }
    }

    #[ArrayShape([ResponseEvent::class => "string"])]
    public static function getSubscribedEvents(): array
    {
        return [
            ResponseEvent::class => 'onKernelResponse'
        ];
    }
}