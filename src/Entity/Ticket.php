<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('ticket:read')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[Groups('ticket:read')]
    private ?Service $service = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('ticket:read')]
    private ?User $creator = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('ticket:read')]
    private ?Role $assignedRole = null;

    #[ORM\Column(length: 255)]
    #[Groups('ticket:read')]
    private ?string $priority = null;

    #[ORM\Column(length: 255)]
    #[Groups('ticket:read')]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups('ticket:read')]
    private ?string $subject = null;

    #[ORM\Column]
    #[Groups('ticket:read')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, TicketComment>
     */
    #[ORM\OneToMany(targetEntity: TicketComment::class, mappedBy: 'ticket', orphanRemoval: true, cascade: ['persist'])]
    #[Groups('ticket:read')]
    private Collection $ticketComments;

    public function __construct()
    {
        $this->ticketComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    public function getAssignedRole(): ?Role
    {
        return $this->assignedRole;
    }

    public function setAssignedRole(?Role $assignedRole): static
    {
        $this->assignedRole = $assignedRole;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

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
     * @return Collection<int, TicketComment>
     */
    public function getTicketComments(): Collection
    {
        return $this->ticketComments;
    }

    public function addTicketComment(TicketComment $ticketComment): static
    {
        if (!$this->ticketComments->contains($ticketComment)) {
            $this->ticketComments->add($ticketComment);
            $ticketComment->setTicket($this);
        }

        return $this;
    }

    public function removeTicketComment(TicketComment $ticketComment): static
    {
        if ($this->ticketComments->removeElement($ticketComment)) {
            // set the owning side to null (unless already changed)
            if ($ticketComment->getTicket() === $this) {
                $ticketComment->setTicket(null);
            }
        }

        return $this;
    }
}
