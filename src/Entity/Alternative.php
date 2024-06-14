<?php

namespace App\Entity;

use App\Repository\AlternativeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlternativeRepository::class)]
class Alternative
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $texte = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(inversedBy: 'alternatives')]
    private ?Etape $etapePrecedente = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etape $etapeSuivante = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): static
    {
        $this->texte = $texte;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function __toString()
    {
        return $this->libelle;
    }

    public function getEtapePrecedente(): ?Etape
    {
        return $this->etapePrecedente;
    }

    public function setEtapePrecedente(?Etape $etapePrecedente): static
    {
        $this->etapePrecedente = $etapePrecedente;

        return $this;
    }

    public function getEtapeSuivante(): ?Etape
    {
        return $this->etapeSuivante;
    }

    public function setEtapeSuivante(?Etape $etapeSuivante): static
    {
        $this->etapeSuivante = $etapeSuivante;

        return $this;
    }
}
