<?php

namespace App\Controller;

use App\Entity\User;
use App\Utils\HTTPResponseHandler;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @Route("", name= "app_user")]
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }
    /**
     * @Route("/register", name="register_user", methods={"POST"})]
     */
    public function register(
        Request $request,
        ManagerRegistry $orm,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $user = $this->getUserFromRequestBody($request, $passwordHasher);
        if(is_null($user)){
            $this->addError(
                Response::HTTP_NOT_FOUND,
                "No se ha encontrado ningún usuario con el id indicado"
            );
        } else{
            $this->persist($user, $orm);
        }
        return $this->generateResponse(
            $user,
            Request::METHOD_POST,
            Response::HTTP_CREATED
        );
    }

    private function getUserFromRequestBody(
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
            "No se ha enviado una canción con un formato adecuado"
        );
        return null;
    }

    private function isUserDataComplete(array $data): bool
    {
        return isset(
            $receivedUser,
            $receivedUser["password"],
            $receivedUser["user"],
            $receivedUser["user"]["userName"]
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
                    $safe = preg_match($dataRegExps[key($data)], current($data)) == 1;
                } else {
                    $safe = preg_match($dataRegExps["default"], current($data)) == 1;
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
        } catch (Exception){
            $this->addError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                "No se ha podido guardar el usuario por un error en el servidor"
            );
        }

    }
}
