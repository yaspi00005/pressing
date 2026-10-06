<?php

namespace App\Controller;

use App\Entity\Clients;
use App\Form\ClientsType;
use App\Repository\ClientsRepository;
use App\Repository\CommandeRepository;
use App\Repository\JournalRepository;
use App\Service\ExportCsv;
use App\Service\Journalisation;
use App\Service\Paginateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/clients')]
class ClientsController extends AbstractController
{
    #[Route('/', name: 'app_clients_index', methods: ['GET'])]
    public function index(Request $request, ClientsRepository $clientsRepository, Paginateur $paginateur): Response
    {
        $q = trim((string) $request->query->get('q'));
        $genre = (string) $request->query->get('genre');
        $pagination = $paginateur->paginer($clientsRepository->filtre($q ?: null, $genre ?: null), $request);

        return $this->render('clients/index.html.twig', [
            'pagination' => $pagination,
            'stats' => $clientsRepository->statistiques(array_map(static fn (Clients $c) => $c->getId(), $pagination->items)),
            'filtres' => ['q' => $q, 'genre' => $genre],
        ]);
    }

    #[Route('/export', name: 'app_clients_export', methods: ['GET'])]
    public function export(Request $request, ClientsRepository $clientsRepository, ExportCsv $csv): Response
    {
        $clients = $clientsRepository->filtre(trim((string) $request->query->get('q')) ?: null, (string) $request->query->get('genre') ?: null)->setMaxResults(20000)->getQuery()->getResult();
        $stats = $clientsRepository->statistiques(array_map(static fn (Clients $c) => $c->getId(), $clients));
        $lignes = (function () use ($clients, $stats) {
            foreach ($clients as $c) {
                yield [$c->getNom(), $c->getPrenom(), $c->getTelephones(), $c->getEmail(), $c->getAdresses(), $c->getGenres(), $stats[$c->getId()]['nb'] ?? 0, $stats[$c->getId()]['solde'] ?? 0, $c->getPointsFidelite()];
            }
        })();

        return $csv->repondre('clients', ['Nom', 'Prénom', 'Téléphone', 'Courriel', 'Adresse', 'Genre', 'Commandes', 'Solde dû', 'Points fidélité'], $lignes);
    }

    #[Route('/new', name: 'app_clients_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Journalisation $journal): Response
    {
        $client = new Clients();
        $form = $this->createForm(ClientsType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($client);
            $entityManager->flush();
            $journal->noter('creation', 'Client créé : '.$client->getNomComplet(), 'client', $client->getId());
            $entityManager->flush();
            $this->addFlash('success', 'Client « '.$client->getNomComplet().' » enregistré.');

            return $this->redirectToRoute('app_clients_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('clients/new.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ], new Response(status: $form->isSubmitted() ? 422 : 200));
    }

    #[Route('/{id}', name: 'app_clients_show', methods: ['GET'])]
    public function show(Clients $client, CommandeRepository $commandes, JournalRepository $journal): Response
    {
        $historique = $commandes->rechercher(null, null, false, false, $client);

        return $this->render('clients/show.html.twig', [
            'client' => $client,
            'journal' => $journal->pourCible('client', $client->getId()),
            'commandes' => $historique,
            'total_depense' => array_sum(array_map(static fn ($c) => 'annule' === $c->getStatut() ? 0 : $c->getTotal(), $historique)),
            'solde_du' => array_sum(array_map(static fn ($c) => 'annule' === $c->getStatut() ? 0 : $c->getReste(), $historique)),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_clients_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Clients $client, EntityManagerInterface $entityManager, Journalisation $journal): Response
    {
        $form = $this->createForm(ClientsType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $journal->noter('modification', 'Client modifié : '.$client->getNomComplet(), 'client', $client->getId());
            $entityManager->flush();
            $this->addFlash('success', 'Client mis à jour.');

            return $this->redirectToRoute('app_clients_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('clients/edit.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ], new Response(status: $form->isSubmitted() ? 422 : 200));
    }

    #[Route('/{id}', name: 'app_clients_delete', methods: ['POST'])]
    public function delete(Request $request, Clients $client, EntityManagerInterface $entityManager, CommandeRepository $commandes): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            if ($commandes->count(['client' => $client]) > 0) {
                $this->addFlash('warning', 'Ce client a des commandes : suppression impossible.');

                return $this->redirectToRoute('app_clients_show', ['id' => $client->getId()], Response::HTTP_SEE_OTHER);
            }
            $entityManager->remove($client);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_clients_index', [], Response::HTTP_SEE_OTHER);
    }
}
