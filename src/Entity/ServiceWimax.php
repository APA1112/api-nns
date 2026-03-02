<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
class ServiceWimax extends Service 
{
    #[ORM\Column(length: 45)]
    #[Groups(['service:read', 'client:read'])]
    private ?string $antennaIp = null;

    #[ORM\Column(length: 17)]
    #[Groups(['service:read', 'client:read'])]
    private ?string $antennaMac = null;

    #[ORM\Column(length: 255)]
    #[Groups(['service:read', 'client:read'])]
    private ?string $apName = null;

    #[ORM\Column]
    #[Groups(['service:read', 'client:read'])]
    private ?int $signalStrength = null;

    // Getters y Setters...
    public function getAntennaIp(): ?string
    {
        return $this->antennaIp;
    }

    public function setAntennaIp(?string $antennaIp): static
    {
        $this->antennaIp = $antennaIp;
        return $this;
    }

    public function getAntennaMac(): ?string
    {
        return $this->antennaMac;
    }

    public function setAntennaMac(?string $antennaMac): static
    {
        $this->antennaMac = $antennaMac;
        return $this;
    }

    public function getApName(): ?string
    {
        return $this->apName;
    }

    public function setApName(?string $apName): static
    {
        $this->apName = $apName;
        return $this;
    }

    public function getSignalStrength(): ?int
    {
        return $this->signalStrength;
    }

    public function setSignalStrength(?int $signalStrength): static
    {
        $this->signalStrength = $signalStrength;
        return $this;
    }
}