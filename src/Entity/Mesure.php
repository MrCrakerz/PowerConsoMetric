<?php

namespace App\Entity;

use App\Repository\MesureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MesureRepository::class)]
class Mesure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 30)]
    private $nom;

    #[ORM\Column(type: 'float')]
    private $consommation;

    #[ORM\Column(type: 'float')]
    private $dureeConso;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getConsommation(): ?float
    {
        return $this->consommation;
    }

    public function setConsommation(float $consommation): self
    {
        $this->consommation = $consommation;

        return $this;
    }

    public function getDureeConso(): ?float
    {
        return $this->dureeConso;
    }

    public function setDureeConso(float $dureeConso): self
    {
        $this->dureeConso = $dureeConso;

        return $this;
    }
}
