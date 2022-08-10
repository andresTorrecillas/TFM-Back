<?php

namespace App\Entity;

use App\Repository\BandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: BandRepository::class)]
class Band implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 60)]
    private string $name;

    #[ORM\OneToMany(mappedBy: 'band', targetEntity: Role::class, orphanRemoval: true)]
    private Collection $roles;

    #[ORM\ManyToMany(targetEntity: Song::class, inversedBy: 'bands')]
    private Collection $songs;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->songs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->setBand($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            // set the owning side to null (unless already changed)
            if ($role->getBand() === $this) {
                $role->setBand(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, song>
     */
    public function getSongs(): Collection
    {
        return $this->songs;
    }

    public function addSong(song $song): self
    {
        if (!$this->songs->contains($song)) {
            $this->songs[] = $song;
        }

        return $this;
    }

    public function removeSong(song $song): self
    {
        $this->songs->removeElement($song);

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name
        ];
    }
}
