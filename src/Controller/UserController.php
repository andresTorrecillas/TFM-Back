<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Base64Service;
use App\Service\HTTPResponseHandler;
use App\Service\JWTService;
use App\Service\OrmService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(UserController::ROOT_PATH)
 */
class UserController extends AbstractController
{
    public const ROOT_PATH = "/api/user";
    private RequestStack $requestStack;
    private HTTPResponseHandler $httpHandler;
    private OrmService $orm;

    public function __construct(OrmService $orm, RequestStack $requestStack, HTTPResponseHandler $httpHandler)
    {
        $this->requestStack = $requestStack;
        $this->httpHandler = $httpHandler;
        $this->orm = $orm;
    }

    /**
     * @Route("", name= "api_login")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);

    }
    /**
     * @Route("/register", name="register_user", methods={"POST"})
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $user = $this->getUserFromRequest($request, $passwordHasher);
        if(!is_null($user)){
            $this->orm->persist($user);
        }
        return $this->httpHandler->generateRegisterResponse($user);
    }

    /**
     * @Route("/login", name="login_user", methods={"POST"})
     */
    public function login(
        Request $request,
        OrmService $orm,
        UserPasswordHasherInterface $passwordHasher,
        JWTService $jwtService
    ):Response
    {
        $data = null;
        $logInUser = $this->getLogInUserFromRequest($request);
        if (!is_null($logInUser)){
            $userName = $logInUser["userName"];
            $password = $logInUser["password"];
            $user = $orm->findOneBy(["userName" => $userName], User::class);
            if(isset($user) && $passwordHasher->isPasswordValid($user, $password)){
                $this->saveInSession([
                    "auth" => true,
                    "user" => $user->getUserName()
                ], true);
                $token = $jwtService->generateToken($user);
                $expirationDate = ($_ENV["JWT_TTL"]??7200 + microtime(true))* 1000;
                $expirationDate = round($expirationDate);
                $data = [
                    "token" => $token,
                    "expirationDate" => $expirationDate,
                    "user" => $user
                ];
            } else{
                $this->httpHandler->addError(
                    Response::HTTP_UNAUTHORIZED,
                    "Credenciales incorrectas"
                );
            }
        }
        return $this->httpHandler->generateLoginResponse($data);
    }

    /**
     * @Route("/logout", name="logout_user", methods={"GET"})
     */
    public function logout(): Response
    {
        $session = $this->requestStack->getSession();
        $session->invalidate();
        return $this->httpHandler->generateLogoutResponse();
    }

    /**
     * @Route("", name="options_user", methods={"OPTIONS"})
     * @Route("/login", name="options_login", methods={"OPTIONS"})
     * @Route("/register", name="options_register", methods={"OPTIONS"})
     */
    public function optionsRequest(): Response{
        return $this->httpHandler->generateOptionsResponse();
    }

    private function  getLogInUserFromRequest(Request $request): array|null
    {
        $body = $request->getContent();
        $receivedUser = json_decode($body, true);
        $logInUser = null;
        if(isset($receivedUser, $receivedUser["userName"], $receivedUser["password"])){
            $logInUser = [
                "userName" => $receivedUser["userName"],
                "password" => Base64Service::decode($receivedUser["password"])
            ];
        } else{
            $this->httpHandler->addError(
                Response::HTTP_BAD_REQUEST,
                "The content doesn't have the correct format"
            );
        }
        return $logInUser;
    }

    private function getUserFromRequest(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): User|null
    {
        $body = $request->getContent();
        $receivedUser = json_decode($body, true);
        if ($this->isUserDataComplete($receivedUser) && $this->isUserDataSafe($receivedUser)) {
            $userArray = $receivedUser["user"];
            $user = new User($userArray["userName"]);
            $user->setName($userArray["name"]??$user->getUserName());
            $plainTextPassword = Base64Service::decode($receivedUser["password"]);
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plainTextPassword
            );
            $user->setPassword($hashedPassword);
            $user->setBandName($userArray["bandName"]??"");
            return $user;
        }
        $this->httpHandler->addError(
            Response::HTTP_BAD_REQUEST,
            "No se ha enviado un usuario con un formato adecuado"
        );
        return null;
    }

    private function isUserDataComplete(array $data): bool
    {
        return isset(
            $data,
            $data["password"],
            $data["user"],
            $data["user"]["userName"]
        );
    }

    private function isUserDataSafe(array $data): bool
    {
        $safe = true;
        $dataRegExps = [
            "default" => '/((SELECT|DELETE|UPDATE|DROP) |[,;\/<>=+\-%|{}\[\]"])/i',
            "password" => '/^[^A-Za-z0-9+\/]+={3,}$/'
        ];
        reset($data);
        while (current($data) && $safe){
            if(is_array(current($data))){
                $safe = $this->isUserDataSafe(current($data));
            } else {
                if (isset($dataRegExps[key($data)])) {
                    $safe = preg_match($dataRegExps[key($data)], current($data)) == 0;
                } else {
                    $safe = preg_match($dataRegExps["default"], current($data)) == 0;
                }
            }
            next($data);
        }
        return $safe;
    }

    private function saveInSession(array $data, bool $invalidate = false){
        $session = $this->requestStack->getSession();
        if($invalidate){
            $session->clear();
            $session->migrate(true);
        }
        foreach ($data as $key => $element){
            $session->set($key, $element);
        }

    }


}
