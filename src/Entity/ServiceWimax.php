<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ServiceWimax extends Service 
{
    #[ORM\Column(length: 45)]
    private ?string $antennaIp = null;

    #[ORM\Column(length: 17)]
    private ?string $antennaMac = null;

    #[ORM\Column(length: 255)]
    private ?string $apName = null;

    #[ORM\Column]
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