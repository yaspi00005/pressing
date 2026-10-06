<?php

namespace App\Twig;

use App\Entity\Commande;
use App\Entity\ProduitStock;
use App\Repository\CommandeRepository;
use App\Repository\ProduitStockRepository;
use App\Repository\ReclamationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\RuntimeExtensionInterface;

/** Compteurs affichés dans la cloche de l'en-tête (chargés seulement quand la page les demande). */
class AlertesRuntime implements RuntimeExtensionInterface
{
    private ?array $cache = null;

    public function __construct(
        private CommandeRepository $commandes,
        private ReclamationRepository $reclamations,
        private ProduitStockRepository $stock,
        private Security $security,
    ) {
    }

    /** @return array{retards: int, pretes: int, impayes: int, reclamations: int, stock: int, total: int} */
    public function alertes(): array
    {
        if (null !== $this->cache) {
            return $this->cache;
        }
        $parStatut = $this->commandes->compterParStatut();
        $reception = $this->security->isGranted('ROLE_RECEPTION');
        $a = [
            'retards' => $this->commandes->compterRetards(),
            'pretes' => $parStatut[Commande::STATUT_PRET] ?? 0,
            'impayes' => $reception ? $this->commandes->impayes()['nb'] : 0,
            'reclamations' => $reception ? $this->reclamations->count(['statut' => 'ouverte']) : 0,
            'stock' => $this->security->isGranted('ROLE_ATELIER') ? \count(array_filter($this->stock->findAll(), static fn (ProduitStock $p) => $p->isEnAlerte())) : 0,
        ];
        $a['total'] = $a['retards'] + $a['reclamations'] + $a['stock'];

        return $this->cache = $a;
    }
}
