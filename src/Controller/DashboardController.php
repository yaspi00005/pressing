<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\ProduitStock;
use App\Repository\ClientsRepository;
use App\Repository\CommandeRepository;
use App\Repository\DepenseRepository;
use App\Repository\PaiementRepository;
use App\Repository\ProduitStockRepository;
use App\Repository\ReclamationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        ClientsRepository $clients,
        CommandeRepository $commandes,
        PaiementRepository $paiements,
        DepenseRepository $depenses,
        ReclamationRepository $reclamations,
        ProduitStockRepository $stock,
    ): Response {
        $aujourdhui = new \DateTimeImmutable('today');
        $demain = $aujourdhui->modify('+1 day');
        $debutMois = new \DateTimeImmutable('first day of this month midnight');
        $moisSuivant = $debutMois->modify('+1 month');

        $enCours = $commandes->enCours();
        $enRetard = array_values(array_filter($enCours, static fn (Commande $c) => $c->isEnRetard()));
        $aRetirer = array_values(array_filter($enCours, static fn (Commande $c) => Commande::STATUT_PRET === $c->getStatut()));
        $impayees = $commandes->impayees();

        // Encaissements des 7 derniers jours (graphique).
        $debutSemaine = $aujourdhui->modify('-6 days');
        $parJour = $paiements->totalParJour($debutSemaine, $demain);
        $graphique = [];
        for ($i = 0; $i < 7; ++$i) {
            $jour = $debutSemaine->modify("+$i days");
            $graphique[] = ['label' => $jour->format('d/m'), 'total' => $parJour[$jour->format('Y-m-d')] ?? 0];
        }

        $encaisseMois = $paiements->totalEntre($debutMois, $moisSuivant);
        $depensesMois = $depenses->totalEntre($debutMois, $moisSuivant);

        return $this->render('dashboard/index.html.twig', [
            'nb_clients' => $clients->count([]),
            'encaisse_jour' => $paiements->totalEntre($aujourdhui, $demain),
            'encaisse_mois' => $encaisseMois,
            'depenses_mois' => $depensesMois,
            'benefice_mois' => $encaisseMois - $depensesMois,
            'depots_jour' => \count($commandes->deposeesEntre($aujourdhui, $demain)),
            'en_cours' => $enCours,
            'en_retard' => $enRetard,
            'a_retirer' => $aRetirer,
            'impayees' => $impayees,
            'total_impaye' => array_sum(array_map(static fn (Commande $c) => $c->getReste(), $impayees)),
            'graphique' => $graphique,
            'top_clients' => $commandes->meilleursClients($debutMois),
            'reclamations_ouvertes' => $reclamations->count(['statut' => 'ouverte']),
            'stock_alerte' => array_values(array_filter($stock->findAll(), static fn (ProduitStock $p) => $p->isEnAlerte())),
        ]);
    }
}
