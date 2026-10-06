<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reclamations')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'app_reclamation_index', methods: ['GET', 'POST'])]
    public function index(Request $request, ReclamationRepository $repo, EntityManagerInterface $em): Response
    {
        $reclamation = new Reclamation();
        $reclamation->setDateCreation(new \DateTimeImmutable());
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($reclamation);
            $em->flush();
            $this->addFlash('success', 'Réclamation enregistrée.');

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $repo->findBy([], ['dateCreation' => 'DESC']),
            'form' => $form->createView(),
            'statuts' => Reclamation::STATUTS,
        ]);
    }

    #[Route('/{id}/traiter', name: 'app_reclamation_traiter', methods: ['POST'])]
    public function traiter(Request $request, Reclamation $reclamation, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('reclamation'.$reclamation->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $statut = (string) $request->request->get('statut');
        if (isset(Reclamation::STATUTS[$statut])) {
            $reclamation->setStatut($statut);
            $reclamation->setResolution(trim((string) $request->request->get('resolution')) ?: null);
            $em->flush();
            $this->addFlash('success', 'Réclamation mise à jour.');
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }
}
