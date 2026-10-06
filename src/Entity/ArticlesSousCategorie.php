<?php

namespace App\Entity;

use App\Repository\ArticlesSousCategorieRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticlesSousCategorieRepository::class)]
class ArticlesSousCategorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $noms = null;

    #[ORM\ManyToOne(inversedBy: 'articlesSousCategories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ArticlesCategories $categorie = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $codeArticles = null;

    #[ORM\Column]
    private ?int $prix = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNoms(): ?string
    {
        return $this->noms;
    }

    public function setNoms(string $noms): static
    {
        $this->noms = $noms;

        return $this;
    }

    public function getCategorie(): ?ArticlesCategories
    {
        return $this->categorie;
    }

    public function setCategorie(?ArticlesCategories $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getCodeArticles(): ?string
    {
        return $this->codeArticles;
    }

    public function setCodeArticles(?string $codeArticles): static
    {
        $this->codeArticles = $codeArticles;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }
}
