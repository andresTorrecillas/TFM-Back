<?php

namespace App\Service;

use App\Entity\BandUser;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTService
{
    private JWTTokenManagerInterface $JWTManager;
    private OrmService $orm;
    public function __construct(JWTTokenManagerInterface $JWTManager, OrmService $orm)
    {
        $this->JWTManager = $JWTManager;
        $this->orm = $orm;
    }

    public function generateToken(UserInterface $user): string{
        return $this->JWTManager->create($user);
    }

    public function generateTokenByUserName(string $userName): string|null{
        $user = $this->orm->findOneBy(["userName" => $userName], BandUser::class);
        return is_null($user)?null:$this->generateToken($user);
    }

}