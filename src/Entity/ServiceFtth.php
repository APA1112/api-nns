<?php

// src/Entity/ServiceFtth.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
class ServiceFtth extends Service 
{
    #[ORM\Column(length: 17)]
    #[Groups(['service:read', 'client:read'])]
    private ?string $ontMac = null;

    #[ORM\Column(length: 50)]
    #[Groups(['service:read', 'client:read'])]
    private ?string $ponPort = null;

    #[ORM\Column(length: 50)]
    #[Groups(['service:read', 'client:read'])]
    private ?string $splitterId = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    #[Groups(['service:read', 'client:read'])]
    private ?string $opticalPower = null;

    // Getters y Setters...
    public function getOntMac(): ?string
    {
        return $this->ontMac;
    }
    
    public function setOntMac(?string $ontMac): self
    {
        $this->ontMac = $ontMac;
        return $this;
    }

    public function getPonPort(): ?string
    {
        return $this->ponPort;
    }

    public function setPonPort(?string $ponPort): self
    {
        $this->ponPort = $ponPort;
        return $this;
    }

    public function getSplitterId(): ?string
    {
        return $this->splitterId;
    }

    public function setSplitterId(?string $splitterId): self
    {
        $this->splitterId = $splitterId;
        return $this;
    }

    public function getOpticalPower(): ?string
    {
        return $this->opticalPower;
    }

    public function setOpticalPower(?string $opticalPower): self
    {
        $this->opticalPower = $opticalPower;
        return $this;
    }
}