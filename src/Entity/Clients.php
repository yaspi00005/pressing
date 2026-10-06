<?php

namespace App\Entity;

use App\Repository\ClientsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientsRepository::class)]
class Clients
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $prenom = null;

    #[ORM\Column(length: 30)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $telephones = null;

    #[ORM\Column(length: 15)]
    private ?string $genres = null;


    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $adresses = null;

    #[ORM\Column]
    private ?bool $sendMessage = true;

    #[ORM\Column(options: ['default' => 0])]
    private int $pointsFidelite = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getNomComplet(): string
    {
        return trim($this->prenom.' '.$this->nom);
    }

    public function __toString(): string
    {
        return $this->getNomComplet();
    }

    public function getPointsFidelite(): int
    {
        return $this->pointsFidelite;
    }

    public function setPointsFidelite(int $pointsFidelite): static
    {
        $this->pointsFidelite = max(0, $pointsFidelite);

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getTelephones(): ?int
    {
        return $this->telephones;
    }

    public function setTelephones(int $telephones): static
    {
        $this->telephones = $telephones;

        return $this;
    }

    public function getGenres(): ?string
    {
        return $this->genres;
    }

    public function setGenres(string $genres): static
    {
        $this->genres = $genres;

        return $this;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAdresses(): ?string
    {
        return $this->adresses;
    }

    public function setAdresses(string $adresses): static
    {
        $this->adresses = $adresses;

        return $this;
    }

    public function isSendMessage(): ?bool
    {
        return $this->sendMessage;
    }

    public function setSendMessage(bool $sendMessage): static
    {
        $this->sendMessage = $sendMessage;

        return $this;
    }
}
