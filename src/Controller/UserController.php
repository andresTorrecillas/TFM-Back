<?php

namespace App\Controller;

use App\Entity\User;
use App\Utils\HTTPResponseHandler;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
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
    use HTTPResponseHandler;
    public const ROOT_PATH = "/api/user";
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
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
        ManagerRegistry $orm,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $user = $this->getUserFromRequest($request, $passwordHasher);
        if(!is_null($user)){
            $this->persist($user, $orm);
        }
        return $this->generateResponse(
            $user,
            Request::METHOD_POST,
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/login", name="login_user", methods={"POST"})
     */
    public function login(
        Request $request,
        ManagerRegistry $orm,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $JWTManager
    ):Response
    {
        $data = null;
        $logInUser = $this->getLogInUserFromRequest($request);
        if (is_null($logInUser)){
            $this->addError(
                Response::HTTP_BAD_REQUEST,
                "The content doesn't have the correct format"
            );
        } else{
            $userName = $logInUser["userName"];
            $password = $logInUser["password"];
            $db = $orm->getRepository(User::class);
            $user = $db->findOneBy(["userName" => $userName]);
            if($passwordHasher->isPasswordValid($user, $password)){
                $session = $this->requestStack->getSession();
                $session->invalidate();
                $session->set("auth", true);
                $session->set("user", $user->getUserName());
                $session->set("roles", $user->getRoles());
                $data = ["token" => $JWTManager->create($user), "user" => $user];
            } else{
                $this->addError(Response::HTTP_UNAUTHORIZED, "Credenciales incorrectas");
            }
        }
        return $this->generateResponse($data,Request::METHOD_POST);
    }

    /**
     * @Route("", name="options_user", methods={"OPTIONS"})
     * @Route("/login", name="options_login", methods={"OPTIONS"})
     */
    public function optionsRequest(): Response{
        return $this->generateResponse(method: Request::METHOD_OPTIONS);
    }

    private function  getLogInUserFromRequest(Request $request): array|null
    {
        $body = $request->getContent();
        $receivedUser = json_decode($body, true);
        $logInUser = null;
        if(isset($receivedUser, $receivedUser["userName"], $receivedUser["password"])){
            $logInUser = [
                "userName" => $receivedUser["userName"],
                "password" => base64_decode($receivedUser["password"])
            ];
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
            $plainTextPassword = base64_decode($receivedUser["password"]);
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plainTextPassword
            );
            $user->setPassword($hashedPassword);
            return $user;
        }
        $this->addError(
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
            "default" => '/((SELECT|DELETE|UPDATE|DROP) |[,;\/<>=+\-%|{}\[\]"])/i'
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

    private function persist(User $user, ManagerRegistry $orm): void
    {
        try {
            $db = $orm->getRepository(User::class);
            $db->add($user, true);
        } catch (Exception $e){
            echo $e;
            $this->addError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                "No se ha podido guardar el usuario por un error en el servidor"
            );
        }

    }


}
