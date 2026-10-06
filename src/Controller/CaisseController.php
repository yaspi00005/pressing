<?php

namespace App\Controller;

use App\Entity\Paiement;
use App\Repository\DepenseRepository;
use App\Repository\PaiementRepository;
use App\Repository\UserRepository;
use App\Service\ExportCsv;
use App\Service\Paginateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CaisseController extends AbstractController
{
    /** Paiements : totaux, filtres (dates, mode, caissier) et liste ; par défaut la journée en cours. */
    #[Route('/caisse', name: 'app_caisse', methods: ['GET'])]
    public function index(Request $request, PaiementRepository $paiements, DepenseRepository $depenses, UserRepository $users, Paginateur $paginateur): Response
    {
        $f = $this->filtres($request);
        $resume = $paiements->resume($f);
        $totalDepenses = array_sum(array_map(static fn ($d) => $d->getMontant(), $depenses->entre($f['du'], $f['au']->modify('+1 day'))));

        return $this->render('caisse/index.html.twig', [
            'pagination' => $paginateur->paginer($paiements->filtre($f), $request),
            'resume' => $resume,
            'totalDepenses' => $totalDepenses,
            'solde' => $resume['total'] - $totalDepenses,
            'modes' => Paiement::MODES,
            'caissiers' => $users->findBy([], ['nom' => 'ASC']),
            'filtres' => ['du' => $f['du']->format('Y-m-d'), 'au' => $f['au']->format('Y-m-d'), 'mode' => $f['mode'] ?? '', 'user' => $f['user'] ?? '', 'q' => $f['q'] ?? ''],
            'aujourdhui' => (new \DateTimeImmutable('today'))->format('Y-m-d'),
        ]);
    }

    #[Route('/caisse/export', name: 'app_caisse_export', methods: ['GET'])]
    public function export(Request $request, PaiementRepository $paiements, ExportCsv $csv): Response
    {
        $liste = $paiements->filtre($this->filtres($request))->setMaxResults(20000)->getQuery()->getResult();
        $lignes = (function () use ($liste) {
            foreach ($liste as $p) {
                yield [$p->getDatePaiement(), $p->getCommande()->getNumero(), $p->getCommande()->getClient()->getNomComplet(), $p->getModeLabel(), $p->getReference(), $p->getMontant(), $p->getCreatedBy()?->getNomComplet()];
            }
        })();

        return $csv->repondre('paiements', ['Date', 'Commande', 'Client', 'Mode', 'Référence', 'Montant', 'Caissier'], $lignes);
    }

    /** @return array{du: \DateTimeImmutable, au: \DateTimeImmutable, mode: ?string, user: ?int, q: ?string} */
    private function filtres(Request $request): array
    {
        $aujourdhui = new \DateTimeImmutable('today');
        $date = static fn (string $cle) => \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $request->query->get($cle)) ?: null;
        $du = $date('du') ?? $aujourdhui;
        $au = $date('au') ?? $du;

        return [
            'du' => $du,
            'au' => $au < $du ? $du : $au,
            'mode' => (string) $request->query->get('mode') ?: null,
            'user' => $request->query->getInt('user') ?: null,
            'q' => trim((string) $request->query->get('q')) ?: null,
        ];
    }
}
