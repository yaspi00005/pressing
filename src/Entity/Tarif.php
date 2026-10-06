<?php

namespace App\Entity;

use App\Repository\TarifRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TarifRepository::class)]
#[ORM\UniqueConstraint(columns: ['article_id', 'service_id'])]
class Tarif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ArticlesSousCategorie::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?ArticlesSousCategorie $article = null;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Service $service = null;

    #[ORM\Column()]
    private ?int $prix = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticle(): ?ArticlesSousCategorie
    {
        return $this->article;
    }

    public function setArticle(ArticlesSousCategorie $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(Service $service): static
    {
        $this->service = $service;

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
}
