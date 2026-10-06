<?php

namespace App\Controller;

use App\Entity\Depense;
use App\Form\DepenseType;
use App\Repository\DepenseRepository;
use App\Service\Journalisation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/depenses')]
class DepenseController extends AbstractController
{
    #[Route('/', name: 'app_depense_index', methods: ['GET', 'POST'])]
    public function index(Request $request, DepenseRepository $repo, EntityManagerInterface $em, Journalisation $journal): Response
    {
        $mois = \DateTimeImmutable::createFromFormat('!Y-m', (string) $request->query->get('mois')) ?: new \DateTimeImmutable('first day of this month midnight');
        $fin = $mois->modify('+1 month');

        $depense = new Depense();
        $depense->setDateDepense(new \DateTimeImmutable('today'));
        $form = $this->createForm(DepenseType::class, $depense);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($depense);
            $em->flush();
            $journal->noter('creation', 'Dépense : '.$depense->getLibelle().' ('.$depense->getMontant().' FCFA)', 'depense', $depense->getId());
            $em->flush();
            $this->addFlash('success', 'Dépense enregistrée.');

            return $this->redirectToRoute('app_depense_index', ['mois' => $depense->getDateDepense()->format('Y-m')], Response::HTTP_SEE_OTHER);
        }

        $depenses = $repo->entre($mois, $fin);
        $parCategorie = [];
        foreach ($depenses as $d) {
            $parCategorie[$d->getCategorieLabel()] = ($parCategorie[$d->getCategorieLabel()] ?? 0) + $d->getMontant();
        }

        return $this->render('depense/index.html.twig', [
            'depenses' => $depenses,
            'mois' => $mois,
            'total' => array_sum($parCategorie),
            'parCategorie' => $parCategorie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_depense_delete', methods: ['POST'])]
    public function delete(Request $request, Depense $depense, EntityManagerInterface $em, Journalisation $journal): Response
    {
        if ($this->isCsrfTokenValid('depense'.$depense->getId(), (string) $request->request->get('_token'))) {
            $journal->noter('suppression', 'Dépense supprimée : '.$depense->getLibelle().' ('.$depense->getMontant().' FCFA)', 'depense', $depense->getId());
            $em->remove($depense);
            $em->flush();
            $this->addFlash('success', 'Dépense supprimée.');
        }

        return $this->redirectToRoute('app_depense_index', ['mois' => $depense->getDateDepense()->format('Y-m')], Response::HTTP_SEE_OTHER);
    }
}
