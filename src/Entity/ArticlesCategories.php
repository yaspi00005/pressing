<?php

namespace App\Entity;

use App\Repository\ArticlesCategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticlesCategoriesRepository::class)]
class ArticlesCategories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $noms = null;

    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: ArticlesSousCategorie::class, orphanRemoval: true)]
    private Collection $articlesSousCategories;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $url = null;

    public function __construct()
    {
        $this->articlesSousCategories = new ArrayCollection();
        $this->url = bin2hex(random_bytes(8));
    }

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

    /**
     * @return Collection<int, ArticlesSousCategorie>
     */
    public function getArticlesSousCategories(): Collection
    {
        return $this->articlesSousCategories;
    }

    public function addArticlesSousCategory(ArticlesSousCategorie $articlesSousCategory): static
    {
        if (!$this->articlesSousCategories->contains($articlesSousCategory)) {
            $this->articlesSousCategories->add($articlesSousCategory);
            $articlesSousCategory->setCategorie($this);
        }

        return $this;
    }

    public function removeArticlesSousCategory(ArticlesSousCategorie $articlesSousCategory): static
    {
        if ($this->articlesSousCategories->removeElement($articlesSousCategory)) {
            // set the owning side to null (unless already changed)
            if ($articlesSousCategory->getCategorie() === $this) {
                $articlesSousCategory->setCategorie(null);
            }
        }

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
