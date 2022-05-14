<?php

namespace App\Entity;

use App\Repository\SongRepository;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

#[ORM\Entity(repositoryClass: SongRepository::class)]
class Song implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 60)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $lyrics;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLyrics(): ?string
    {
        return $this->lyrics;
    }

    public function setLyrics(?string $lyrics): self
    {
        $this->lyrics = $lyrics;

        return $this;
    }

    #[ArrayShape(['id' => "int", 'title' => "string", 'lyrics' => "null|string"])]
    public function jsonSerialize(): array
    {
        return array(
            'id'=>$this->id??0,
            'title'=>$this->title,
            'lyrics'=>$this->lyrics
        );
    }
}
