<?php

namespace App\Service;

use App\Entity\BandUser;

class AuthService
{
    private BandUser $user;

    public function __construct(SessionService $session, OrmService $orm)
    {
        $userName = $session->readFromSession('user');
        if(isset($userName)){
            $this->user = $orm->findOneBy(['userName' => $userName], BandUser::class);
        }
    }

    /**
     * @return BandUser
     */
    public function getUser(): BandUser
    {
        return $this->user;
    }

    /**
     * @param BandUser $user
     */
    public function setUser(BandUser $user): void
    {
        $this->user = $user;
    }

    public function isBandMember(string $band): bool
    {
        return in_array($band, $this->user->getBandNamesList());
    }

}