<?php

namespace App\Controller;

use App\Entity\Song;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SongController extends AbstractController
{
    /**
     * @Route("/song", name="app_song", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SongController.php',
        ]);
    }
    /**
     * @Route("/new/song", name="new_song")
     */
    public function createSong(ManagerRegistry $orm): Response
    {
        $db = $orm->getRepository(Song::class);
        $song = new Song();
        $song->setTitle("Nueva")->setLyrics("Esto es algo más largo pero no me voy a venir arriba");
        $db->add($song, true);
        return $this->json($song);
    }
}
