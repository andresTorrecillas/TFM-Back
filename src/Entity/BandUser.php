<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class BandUser implements UserInterface, PasswordAuthenticatedUserInterface, JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $uuid;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'string', length: 60)]
    private string $name;

    #[ORM\Column(type: 'string', length: 60, unique: true)]
    private string $userName;

    #[ORM\Column(type: 'string', length: 60)]
    private string $bandName;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Role::class)]
    private PersistentCollection $bandRoles;



    public function __construct(string $userName)
    {
        $this->id = 0;
        $this->uuid = Uuid::v3(Uuid::fromString(Uuid::NAMESPACE_URL), $userName)->toBase32();
        $this->userName = $userName;
        $this->name = $userName;
        $this->bandName = '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }
    /**
     * @codeCoverageIgnore
     */
    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     * @codeCoverageIgnore
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->name;
    }

    /**
     * @see UserInterface
     * @codeCoverageIgnore
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }
    /**
     * @codeCoverageIgnore
     */
    public function setRoles(array $roles): self
    {
        $this->roles = array_unique($roles);

        return $this;
    }
    /**
     * @codeCoverageIgnore
     */
    public function addRole(string $role): self
    {
        $this->roles[] = $role;
        $this->roles = array_unique($this->roles);

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     * @codeCoverageIgnore
     */
    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getName(): ?string
    {
        return $this->name;
    }
    /**
     * @codeCoverageIgnore
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }
    /**
     * @codeCoverageIgnore
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getBandName(): string
    {
        return $this->bandName;
    }

    public function getBandNamesList(): array
    {
        $bandNames = [];
        foreach ($this->bandRoles as $role){
            $bandNames[] = $role->getBand()->getName();
        }
        return $bandNames;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setBandName(string $band_name): self
    {
        $this->bandName = $band_name;

        return $this;
    }

    /**
     * @see UserInterface
     * @codeCoverageIgnore
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


    #[ArrayShape(["id" => "int", "uuid" => "string", "name" => "string", "userName" => "string", "bandName" => "string", "bands" => "string"])]
    public function jsonSerialize(): array
    {
        return [
            "id" => $this->id,
            "uuid" => $this->uuid,
            "name" => $this->name,
            "userName" => $this->userName,
            "bandName" => $this->bandName,
            "bands" => $this->getBandNamesList()
        ];
    }

    public function addRoles(Role $roles): self
    {
        if (!$this->bandRoles->contains($roles)) {
            $this->bandRoles[] = $roles;
            $roles->setUser($this);
        }

        return $this;
    }

    public function removeRoles(Role $roles): self
    {
        if ($this->bandRoles->removeElement($roles)) {
            // set the owning side to null (unless already changed)
            if ($roles->getUser() === $this) {
                $roles->setUser(null);
            }
        }

        return $this;
    }
}
