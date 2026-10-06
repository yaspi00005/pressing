<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GuichetController extends AbstractController
{
    /**
     * Guichet : retrouver vite une commande (n° de ticket saisi ou scanné à la douchette, nom, téléphone).
     * Un n° exact ouvre directement la fiche ; sinon on liste les correspondances.
     */
    #[Route('/guichet', name: 'app_guichet', methods: ['GET'])]
    public function index(Request $request, CommandeRepository $repo): Response
    {
        $q = trim((string) $request->query->get('q'));
        $resultats = [];

        if ('' !== $q) {
            $exacte = $repo->findOneBy(['numero' => strtoupper($q)]);
            if ($exacte instanceof Commande) {
                return $this->redirectToRoute('app_commande_show', ['id' => $exacte->getId()]);
            }
            $resultats = $repo->filtre(['q' => $q])->setMaxResults(30)->getQuery()->getResult();
        }

        return $this->render('guichet/index.html.twig', [
            'q' => $q,
            'resultats' => $resultats,
            'a_retirer' => '' === $q ? $repo->filtre(['statut' => Commande::STATUT_PRET])->orderBy('c.dateRetraitPrevue', 'ASC')->setMaxResults(15)->getQuery()->getResult() : [],
        ]);
    }
}
