<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection; // CORREGIDO: Import de Doctrine, no de Validator
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Sugerencia: añadir unique: true para evitar duplicados
    #[ORM\Column(length: 255, unique: true)] 
    private ?string $name = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(mappedBy: 'userRole', targetEntity: User::class)]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setUserRole($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getUserRole() === $this) {
                $user->setUserRole(null);
            }
        }

        return $this;
    }

    // TIP: Este método ayuda mucho en los formularios de Symfony
    public function __toString(): string
    {
        return (string) $this->name;
    }
}