<?php

namespace App\Controller;

use App\Entity\Paiement;
use App\Repository\DepenseRepository;
use App\Repository\PaiementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CaisseController extends AbstractController
{
    /** Rapport de caisse d'une journée : encaissements par mode, dépenses, solde. */
    #[Route('/caisse', name: 'app_caisse', methods: ['GET'])]
    public function index(Request $request, PaiementRepository $paiements, DepenseRepository $depenses): Response
    {
        $jour = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $request->query->get('jour')) ?: new \DateTimeImmutable('today');
        $suivant = $jour->modify('+1 day');

        $encaissements = $paiements->entre($jour, $suivant);
        $depensesJour = $depenses->entre($jour, $suivant);
        $totalEncaisse = array_sum(array_map(static fn (Paiement $p) => $p->getMontant(), $encaissements));
        $totalDepenses = array_sum(array_map(static fn ($d) => $d->getMontant(), $depensesJour));

        return $this->render('caisse/index.html.twig', [
            'jour' => $jour,
            'encaissements' => $encaissements,
            'depenses' => $depensesJour,
            'parMode' => $paiements->totalParMode($jour, $suivant),
            'totalEncaisse' => $totalEncaisse,
            'totalDepenses' => $totalDepenses,
            'solde' => $totalEncaisse - $totalDepenses,
            'modes' => Paiement::MODES,
        ]);
    }
}
