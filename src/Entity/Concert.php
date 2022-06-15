<?php

namespace App\Entity;

use App\Repository\ConcertRepository;
use App\Service\Base64Service;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConcertRepository::class)]
class Concert
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 30, unique: true)]
    #[ORM\GeneratedValue(strategy: "NONE")]
    private string $id;

    #[ORM\Column(type: 'string', length: 9)]
    private ?string $color;

    #[ORM\Column(type: 'string', length: 60)]
    private ?string $state;

    #[ORM\Column(type: 'datetime')]
    private ?DateTime $date;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $location;

    #[ORM\Column(type: 'string', length: 120)]
    private ?string $modality;

    #[ORM\Column(type: 'json')]
    private ?string $coordinates;

    public function __construct()
    {
        $this->id = Base64Service::encode(uniqid());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getModality(): ?string
    {
        return $this->modality;
    }

    public function setModality(?string $modality): self
    {
        $this->modality = $modality;

        return $this;
    }

    public function getCoordinates(): ?string
    {
        return $this->coordinates;
    }

    public function setCoordinates(?string $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }
}
