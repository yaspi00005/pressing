<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\ClientsRepository;
use App\Repository\CommandeRepository;
use App\Repository\DepenseRepository;
use App\Repository\PaiementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    private const MOIS = ['01' => 'janv.', '02' => 'févr.', '03' => 'mars', '04' => 'avr.', '05' => 'mai', '06' => 'juin', '07' => 'juil.', '08' => 'août', '09' => 'sept.', '10' => 'oct.', '11' => 'nov.', '12' => 'déc.'];

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(ClientsRepository $clients, CommandeRepository $commandes, PaiementRepository $paiements, DepenseRepository $depenses): Response
    {
        $aujourdhui = new \DateTimeImmutable('today');
        $demain = $aujourdhui->modify('+1 day');
        $debutMois = new \DateTimeImmutable('first day of this month midnight');
        $moisSuivant = $debutMois->modify('+1 month');
        $reception = $this->isGranted('ROLE_RECEPTION');

        $parStatut = $commandes->compterParStatut();
        $impayes = $commandes->impayes();

        // Commandes déposées par mois (12 derniers mois) : nombre et chiffre d'affaires.
        $debut12 = $debutMois->modify('-11 months');
        $parMois = $commandes->parMois($debut12, $moisSuivant);
        $mois = [];
        for ($i = 0; $i < 12; ++$i) {
            $m = $debut12->modify("+$i months");
            $mois[] = ['label' => self::MOIS[$m->format('m')].' '.$m->format('y'), 'nb' => $parMois[$m->format('Y-m')]['nb'] ?? 0, 'total' => $parMois[$m->format('Y-m')]['total'] ?? 0];
        }

        // Encaissements des 7 derniers jours.
        $debutSemaine = $aujourdhui->modify('-6 days');
        $parJour = $reception ? $paiements->totalParJour($debutSemaine, $demain) : [];
        $semaine = [];
        for ($i = 0; $i < 7; ++$i) {
            $j = $debutSemaine->modify("+$i days");
            $semaine[] = ['label' => $j->format('d/m'), 'total' => $parJour[$j->format('Y-m-d')] ?? 0];
        }

        $encaisseMois = $reception ? $paiements->totalEntre($debutMois, $moisSuivant) : 0;
        $depensesMois = $this->isGranted('ROLE_ADMIN') ? $depenses->totalEntre($debutMois, $moisSuivant) : 0;

        return $this->render('dashboard/index.html.twig', [
            'nb_clients' => $clients->count([]),
            'encaisse_jour' => $reception ? $paiements->totalEntre($aujourdhui, $demain) : 0,
            'encaisse_mois' => $encaisseMois,
            'depenses_mois' => $depensesMois,
            'resultat_mois' => $encaisseMois - $depensesMois,
            'ca_mois' => $commandes->chiffreAffaires($debutMois, $moisSuivant),
            'depots_jour' => $commandes->compterDeposees($aujourdhui, $demain),
            'depots_mois' => $commandes->compterDeposees($debutMois, $moisSuivant),
            'nb_en_cours' => ($parStatut[Commande::STATUT_RECU] ?? 0) + ($parStatut[Commande::STATUT_EN_TRAITEMENT] ?? 0),
            'nb_pretes' => $parStatut[Commande::STATUT_PRET] ?? 0,
            'nb_retards' => $commandes->compterRetards(),
            'impayes' => $impayes,
            'a_traiter' => $commandes->enCours(10),
            'top_clients' => $reception ? $commandes->meilleursClients($debutMois) : [],
            'par_statut' => $parStatut,
            'mois' => $mois,
            'semaine' => $semaine,
        ]);
    }
}
