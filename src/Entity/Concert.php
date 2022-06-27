<?php

namespace App\Entity;

use App\Repository\ConcertRepository;
use App\Service\Base64Service;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use JsonSerializable;

#[ORM\Entity(repositoryClass: ConcertRepository::class)]
class Concert implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 60, unique: true)]
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

    public function __construct()
    {
        $this->id = Base64Service::url_encode(uniqid(more_entropy: true));
        $this->color = '#00000000';
        $this->state = 'Created';
        $this->date = new DateTime();
        $this->address = '';
        $this->modality = 'Base';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setName(string $name): Concert
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setDate(DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getModality(): string
    {
        return $this->modality;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setModality(?string $modality): self
    {
        $this->modality = $modality;

        return $this;
    }

    public function initFromArray(array $data): bool
    {
        foreach ($this as $key => &$value){
            if(!empty($data[$key])){
                if(is_array($data[$key])){
                    try {
                        $dateString =  str_replace('+0000', '', $data[$key]['_date']);
                        $dateTimeZoneString = $data[$key]['_timezone'];
                        $dateTimeZoneString = str_replace('\\', "", $dateTimeZoneString);
                        $dateTimeZone = new DateTimeZone($dateTimeZoneString);
                        $this->date = new DateTime($dateString, $dateTimeZone);
                    } catch (Exception) {
                        return false;
                    }
                } else{
                    $value = $data[$key];
                }
            }
        }
        if(!empty($data['name'])){
            $this->name = $data['name'];
        } else{
            return false;
        }
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function jsonSerialize(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "color" => $this->color,
            "state" => $this->state,
            "date" => $this->date,
            "address" => $this->address,
            "modality" => $this->modality
        ];
    }
}
