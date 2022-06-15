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

    #[ORM\Column(type: 'string', length: 60, unique: true)]
    private string $name;

    #[ORM\Column(type: 'string', length: 9)]
    private string $color;

    #[ORM\Column(type: 'string', length: 60)]
    private string $state;

    #[ORM\Column(type: 'datetime')]
    private DateTime $date;

    #[ORM\Column(type: 'string', length: 255)]
    private string $address;

    #[ORM\Column(type: 'string', length: 120)]
    private string $modality;

    #[ORM\Column(type: 'json')]
    private array $coordinates;

    public function __construct()
    {
        $this->id = Base64Service::url_encode(uniqid(more_entropy: true));
        $this->color = '#00000000';
        $this->state = 'Created';
        $this->date = new DateTime();
        $this->address = '';
        $this->modality = 'Base';
        $this->coordinates = [
            "latitude" => 0,
            "longitude" => 0,
        ];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Concert
    {
        $this->name = $name;
        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getModality(): string
    {
        return $this->modality;
    }

    public function setModality(?string $modality): self
    {
        $this->modality = $modality;

        return $this;
    }

    public function getCoordinates(): array
    {
        return $this->coordinates;
    }

    public function setCoordinates(array $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }
}
