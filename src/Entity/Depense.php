<?php

namespace App\Entity;

use App\Repository\DepenseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepenseRepository::class)]
class Depense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $libelle = null;

    #[ORM\Column(length: 30)]
    private ?string $categorie = null;

    #[ORM\Column()]
    private ?int $montant = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $dateDepense = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public const CATEGORIES = [
        'produits' => 'Produits & consommables',
        'electricite' => 'Électricité / eau',
        'loyer' => 'Loyer',
        'salaires' => 'Salaires',
        'transport' => 'Transport / livraison',
        'maintenance' => 'Maintenance machines',
        'autre' => 'Autre',
    ];

    public function getCategorieLabel(): string
    {
        return self::CATEGORIES[$this->categorie] ?? (string) $this->categorie;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getMontant(): ?int
    {
        return $this->montant;
    }

    public function setMontant(int $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDateDepense(): ?\DateTimeImmutable
    {
        return $this->dateDepense;
    }

    public function setDateDepense(\DateTimeImmutable $dateDepense): static
    {
        $this->dateDepense = $dateDepense;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }
}
