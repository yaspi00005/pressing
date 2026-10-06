<?php

namespace App\Controller;

use App\Entity\ProduitStock;
use App\Form\ProduitStockType;
use App\Repository\ProduitStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stock')]
class StockController extends AbstractController
{
    #[Route('/', name: 'app_stock_index', methods: ['GET', 'POST'])]
    public function index(Request $request, ProduitStockRepository $repo, EntityManagerInterface $em): Response
    {
        $produit = new ProduitStock();
        $form = $this->createForm(ProduitStockType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'Produit ajouté au stock.');

            return $this->redirectToRoute('app_stock_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stock/index.html.twig', [
            'produits' => $repo->findBy([], ['nom' => 'ASC']),
            'form' => $form,
        ]);
    }

    /** Entrée (+) ou sortie (-) de stock. */
    #[Route('/{id}/mouvement', name: 'app_stock_mouvement', methods: ['POST'])]
    public function mouvement(Request $request, ProduitStock $produit, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('stock'.$produit->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $quantite = $request->request->getInt('quantite');
        $delta = 'sortie' === $request->request->get('type') ? -$quantite : $quantite;
        if ($quantite <= 0) {
            $this->addFlash('danger', 'Quantité invalide.');
        } elseif ($produit->getQuantite() + $delta < 0) {
            $this->addFlash('danger', 'Stock insuffisant pour cette sortie.');
        } else {
            $produit->setQuantite($produit->getQuantite() + $delta);
            $em->flush();
            $this->addFlash('success', 'Stock de « '.$produit->getNom().' » mis à jour.');
        }

        return $this->redirectToRoute('app_stock_index', [], Response::HTTP_SEE_OTHER);
    }
}
