<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class SessionService
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function saveInSession(array $data, bool $invalidate = false): void
    {
        $session = $this->requestStack->getSession();
        if($invalidate){
            $this->invalidateSession();
        }
        foreach ($data as $key => $element){
            $session->set($key, $element);
        }
    }

    public function invalidateSession(): void
    {
        $session = $this->requestStack->getSession();
        $session->clear();
        $session->migrate(true);
    }

    public function readFromSession(string $key): mixed
    {
        $session = $this->requestStack->getSession();
        return $session->get($key);
    }
}