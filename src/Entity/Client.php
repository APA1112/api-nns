<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['client:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read'])]
    #[Assert\NotBlank]
    #[Assert\Length(min:9)]
    #[Assert\Regex(pattern: '/^[0-9]{8}[TRWAGMYFPDXBNJZSQVHLCKE]$/i')]
    private ?string $dni = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read'])]
    #[Assert\NotBlank]
    #[Assert\Length(min:3)]
    private ?string $fullName = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['client:read'])]
    #[Assert\NotBlank]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read'])]
    #[Assert\NotBlank]
    #[Assert\Length(min:9)]
    #[Assert\Regex(pattern: '/^\+?[0-9\s\-]+$/')]
    private ?string $phone = null;

    #[ORM\Column]
    #[Groups(['client:read'])]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Service>
     */
    #[ORM\OneToMany(targetEntity: Service::class, mappedBy: 'client', orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[Groups(['client:read'])]
    #[Assert\Valid]
    private Collection $services;

    public function __construct()
    {
        $this->services = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(string $dni): static
    {
        $this->dni = $dni;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setClient($this);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getClient() === $this) {
                $service->setClient(null);
            }
        }

        return $this;
    }
}
