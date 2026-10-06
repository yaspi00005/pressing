<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    public const STATUT_RECU = 'recu';
    public const STATUT_EN_TRAITEMENT = 'en_traitement';
    public const STATUT_PRET = 'pret';
    public const STATUT_LIVRE = 'livre';
    public const STATUT_ANNULE = 'annule';

    public const STATUTS = [
        self::STATUT_RECU => 'Reçu',
        self::STATUT_EN_TRAITEMENT => 'En traitement',
        self::STATUT_PRET => 'Prêt à retirer',
        self::STATUT_LIVRE => 'Livré',
        self::STATUT_ANNULE => 'Annulé',
    ];

    public const BADGES = [
        self::STATUT_RECU => 'secondary',
        self::STATUT_EN_TRAITEMENT => 'warning',
        self::STATUT_PRET => 'info',
        self::STATUT_LIVRE => 'success',
        self::STATUT_ANNULE => 'danger',
    ];

    /** Statut suivant dans le circuit normal d'une commande. */
    public const SUIVANT = [
        self::STATUT_RECU => self::STATUT_EN_TRAITEMENT,
        self::STATUT_EN_TRAITEMENT => self::STATUT_PRET,
        self::STATUT_PRET => self::STATUT_LIVRE,
    ];

    public const MODE_RETRAIT = 'retrait';
    public const MODE_DOMICILE = 'domicile';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, unique: true)]
    private ?string $numero = null;

    #[ORM\ManyToOne(targetEntity: Clients::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Clients $client = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $createdBy = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $dateDepot = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $dateRetraitPrevue = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateLivraison = null;

    #[ORM\Column(length: 20)]
    private string $statut = self::STATUT_RECU;

    #[ORM\Column]
    private bool $urgent = false;

    /** Majoration express appliquée à cette commande (en %). */
    #[ORM\Column]
    private int $majorationPourcent = 0;

    #[ORM\Column]
    private int $remise = 0;

    #[ORM\Column(length: 20)]
    private string $modeLivraison = self::MODE_RETRAIT;

    #[ORM\Column]
    private int $fraisLivraison = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresseLivraison = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private bool $pointsCredites = false;

    /** @var Collection<int, LigneCommande> */
    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: LigneCommande::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lignes;

    /** @var Collection<int, Paiement> */
    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: Paiement::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['datePaiement' => 'ASC'])]
    private Collection $paiements;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
        $this->paiements = new ArrayCollection();
        $this->dateDepot = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getClient(): ?Clients
    {
        return $this->client;
    }

    public function setClient(?Clients $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getDateDepot(): ?\DateTimeImmutable
    {
        return $this->dateDepot;
    }

    public function setDateDepot(\DateTimeImmutable $dateDepot): static
    {
        $this->dateDepot = $dateDepot;

        return $this;
    }

    public function getDateRetraitPrevue(): ?\DateTimeImmutable
    {
        return $this->dateRetraitPrevue;
    }

    public function setDateRetraitPrevue(\DateTimeImmutable $dateRetraitPrevue): static
    {
        $this->dateRetraitPrevue = $dateRetraitPrevue;

        return $this;
    }

    public function getDateLivraison(): ?\DateTimeImmutable
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(?\DateTimeImmutable $dateLivraison): static
    {
        $this->dateLivraison = $dateLivraison;

        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getStatutLabel(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getStatutBadge(): string
    {
        return self::BADGES[$this->statut] ?? 'secondary';
    }

    public function getStatutSuivant(): ?string
    {
        return self::SUIVANT[$this->statut] ?? null;
    }

    public function isUrgent(): bool
    {
        return $this->urgent;
    }

    public function setUrgent(bool $urgent): static
    {
        $this->urgent = $urgent;

        return $this;
    }

    public function getMajorationPourcent(): int
    {
        return $this->majorationPourcent;
    }

    public function setMajorationPourcent(int $majorationPourcent): static
    {
        $this->majorationPourcent = max(0, $majorationPourcent);

        return $this;
    }

    public function getRemise(): int
    {
        return $this->remise;
    }

    public function setRemise(int $remise): static
    {
        $this->remise = max(0, $remise);

        return $this;
    }

    public function getModeLivraison(): string
    {
        return $this->modeLivraison;
    }

    public function setModeLivraison(string $modeLivraison): static
    {
        $this->modeLivraison = $modeLivraison;

        return $this;
    }

    public function isLivraisonDomicile(): bool
    {
        return self::MODE_DOMICILE === $this->modeLivraison;
    }

    public function getFraisLivraison(): int
    {
        return $this->fraisLivraison;
    }

    public function setFraisLivraison(int $fraisLivraison): static
    {
        $this->fraisLivraison = max(0, $fraisLivraison);

        return $this;
    }

    public function getAdresseLivraison(): ?string
    {
        return $this->adresseLivraison;
    }

    public function setAdresseLivraison(?string $adresseLivraison): static
    {
        $this->adresseLivraison = $adresseLivraison;

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

    public function isPointsCredites(): bool
    {
        return $this->pointsCredites;
    }

    public function setPointsCredites(bool $pointsCredites): static
    {
        $this->pointsCredites = $pointsCredites;

        return $this;
    }

    /** @return Collection<int, LigneCommande> */
    public function getLignes(): Collection
    {
        return $this->lignes;
    }

    public function addLigne(LigneCommande $ligne): static
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setCommande($this);
        }

        return $this;
    }

    public function removeLigne(LigneCommande $ligne): static
    {
        $this->lignes->removeElement($ligne);

        return $this;
    }

    /** @return Collection<int, Paiement> */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): static
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setCommande($this);
        }

        return $this;
    }

    public function getNombrePieces(): int
    {
        $n = 0;
        foreach ($this->lignes as $ligne) {
            $n += (int) $ligne->getQuantite();
        }

        return $n;
    }

    public function getSousTotal(): int
    {
        $total = 0;
        foreach ($this->lignes as $ligne) {
            $total += $ligne->getMontant();
        }

        return $total;
    }

    public function getMajoration(): int
    {
        return (int) round($this->getSousTotal() * $this->majorationPourcent / 100);
    }

    public function getTotal(): int
    {
        return max(0, $this->getSousTotal() + $this->getMajoration() + $this->fraisLivraison - $this->remise);
    }

    public function getTotalPaye(): int
    {
        $total = 0;
        foreach ($this->paiements as $paiement) {
            $total += (int) $paiement->getMontant();
        }

        return $total;
    }

    public function getReste(): int
    {
        return max(0, $this->getTotal() - $this->getTotalPaye());
    }

    public function isSolde(): bool
    {
        return 0 === $this->getReste();
    }

    public function isTerminee(): bool
    {
        return \in_array($this->statut, [self::STATUT_LIVRE, self::STATUT_ANNULE], true);
    }

    public function isEnRetard(?\DateTimeImmutable $now = null): bool
    {
        return !$this->isTerminee()
            && self::STATUT_PRET !== $this->statut
            && $this->dateRetraitPrevue < ($now ?? new \DateTimeImmutable());
    }
}
