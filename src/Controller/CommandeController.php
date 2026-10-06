<?php

namespace App\Controller;

use App\Entity\ArticlesSousCategorie;
use App\Entity\Commande;
use App\Entity\Paiement;
use App\Form\CommandeType;
use App\Repository\ArticlesSousCategorieRepository;
use App\Repository\ClientsRepository;
use App\Repository\CommandeRepository;
use App\Repository\JournalRepository;
use App\Repository\TarifRepository;
use App\Service\ExportCsv;
use App\Service\Paginateur;
use App\Service\CommandeManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/commandes')]
class CommandeController extends AbstractController
{
    public function __construct(
        private CommandeManager $manager,
        private TarifRepository $tarifs,
        private ArticlesSousCategorieRepository $articles,
        #[Autowire('%app.majoration_express%')] private int $majorationExpress,
        #[Autowire('%app.frais_livraison_defaut%')] private int $fraisLivraisonDefaut,
        #[Autowire('%app.delai_standard_jours%')] private int $delaiStandard,
    ) {
    }

    #[Route('/', name: 'app_commande_index', methods: ['GET'])]
    public function index(Request $request, CommandeRepository $repo, Paginateur $paginateur): Response
    {
        $filtres = $this->filtresDepuisRequete($request);

        return $this->render('commande/index.html.twig', [
            'pagination' => $paginateur->paginer($repo->filtre($filtres), $request),
            'totaux' => $repo->totaux($filtres),
            'compteurs' => $repo->compterParStatut(),
            'filtres' => $request->query->all() + ['q' => '', 'statut' => '', 'filtre' => '', 'du' => '', 'au' => '', 'livraison' => ''],
        ]);
    }

    #[Route('/export', name: 'app_commande_export', methods: ['GET'])]
    public function export(Request $request, CommandeRepository $repo, ExportCsv $csv): Response
    {
        $commandes = $repo->filtre($this->filtresDepuisRequete($request))->setMaxResults(10000)->getQuery()->getResult();
        $lignes = (function () use ($commandes) {
            foreach ($commandes as $c) {
                yield [$c->getNumero(), $c->getDateDepot(), $c->getClient()->getNomComplet(), $c->getClient()->getTelephones(), $c->getDateRetraitPrevue(), $c->getStatutLabel(), $c->isUrgent() ? 'Oui' : 'Non', $c->getNombrePieces(), $c->getTotal(), $c->getTotalPaye(), $c->getReste()];
            }
        })();

        return $csv->repondre('commandes', ['N°', 'Déposée le', 'Client', 'Téléphone', 'Retrait prévu', 'Statut', 'Express', 'Pièces', 'Total', 'Payé', 'Reste'], $lignes);
    }

    #[IsGranted('ROLE_RECEPTION')]
    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ClientsRepository $clients): Response
    {
        $commande = new Commande();
        $commande->setDateRetraitPrevue(new \DateTimeImmutable('+'.$this->delaiStandard.' days'));
        $commande->setFraisLivraison($this->fraisLivraisonDefaut);

        if ($clientId = $request->query->getInt('client')) {
            $client = $clients->find($clientId);
            $commande->setClient($client);
        }

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $acompte = (int) $form->get('acompte')->getData();
            $this->manager->enregistrer($commande, $this->getUser(), $acompte, (string) $form->get('modeAcompte')->getData());
            $this->addFlash('success', 'Commande '.$commande->getNumero().' enregistrée.');

            return $this->redirectToRoute('app_commande_show', ['id' => $commande->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/form.html.twig', $this->formVars($form, $commande, 'Nouvelle commande'), new Response(status: $form->isSubmitted() ? 422 : 200));
    }

    #[IsGranted('ROLE_RECEPTION')]
    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        if ($commande->isTerminee()) {
            $this->addFlash('warning', 'Une commande livrée ou annulée ne peut plus être modifiée.');

            return $this->redirectToRoute('app_commande_show', ['id' => $commande->getId()]);
        }

        $form = $this->createForm(CommandeType::class, $commande, ['avec_acompte' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->enregistrer($commande, $this->getUser());
            $this->addFlash('success', 'Commande mise à jour.');

            return $this->redirectToRoute('app_commande_show', ['id' => $commande->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/form.html.twig', $this->formVars($form, $commande, 'Modifier '.$commande->getNumero()), new Response(status: $form->isSubmitted() ? 422 : 200));
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Commande $commande, JournalRepository $journal): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
            'journal' => $journal->pourCible('commande', $commande->getId()),
            'modes' => Paiement::MODES,
        ]);
    }

    #[Route('/{id}/ticket', name: 'app_commande_ticket', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function ticket(Commande $commande): Response
    {
        return $this->render('commande/ticket.html.twig', ['commande' => $commande]);
    }

    #[Route('/{id}/statut', name: 'app_commande_statut', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function statut(Request $request, Commande $commande): Response
    {
        if (!$this->isCsrfTokenValid('statut'.$commande->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $statut = (string) $request->request->get('statut');
        if (!isset(Commande::STATUTS[$statut])) {
            throw $this->createNotFoundException();
        }
        if (\in_array($statut, [Commande::STATUT_LIVRE, Commande::STATUT_ANNULE], true)) {
            $this->denyAccessUnlessGranted('ROLE_RECEPTION');
        }
        if ($commande->isTerminee()) {
            $this->addFlash('warning', 'Cette commande est déjà clôturée.');
        } elseif (Commande::STATUT_LIVRE === $statut && !$commande->isSolde() && !$request->request->getBoolean('forcer')) {
            $this->addFlash('warning', 'Reste '.number_format($commande->getReste(), 0, ',', ' ').' à encaisser avant la remise du linge (ou confirmez la livraison à crédit).');
        } else {
            $this->manager->changerStatut($commande, $statut);
            $this->addFlash('success', 'Statut : '.$commande->getStatutLabel().'.');
        }

        return $this->redirectToRoute('app_commande_show', ['id' => $commande->getId()], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_RECEPTION')]
    #[Route('/{id}/paiement', name: 'app_commande_paiement', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function paiement(Request $request, Commande $commande): Response
    {
        if (!$this->isCsrfTokenValid('paiement'.$commande->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $montant = $request->request->getInt('montant');
        if ($montant <= 0) {
            $this->addFlash('danger', 'Montant invalide.');
        } else {
            try {
                $this->manager->ajouterPaiement($commande, $montant, (string) $request->request->get('mode'), (string) $request->request->get('reference'), $this->getUser());
                $this->addFlash('success', 'Paiement enregistré.');
            } catch (\DomainException $e) {
                $this->addFlash('warning', $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_commande_show', ['id' => $commande->getId()], Response::HTTP_SEE_OTHER);
    }

    private function formVars($form, Commande $commande, string $titre): array
    {
        $prix = [];
        foreach ($this->tarifs->findAll() as $t) {
            $prix[$t->getArticle()->getId().'-'.$t->getService()->getId()] = $t->getPrix();
        }
        $base = [];
        /** @var ArticlesSousCategorie $a */
        foreach ($this->articles->findAll() as $a) {
            $base[$a->getId()] = $a->getPrix();
        }

        return [
            'form' => $form->createView(),
            'commande' => $commande,
            'titre' => $titre,
            'tarifs' => $prix,
            'prixBase' => $base,
            'majoration' => $this->majorationExpress,
        ];
    }

    /** @return array<string,mixed> */
    private function filtresDepuisRequete(Request $request): array
    {
        $date = static fn (string $cle) => ($d = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $request->query->get($cle))) ? $d : null;

        return [
            'q' => trim((string) $request->query->get('q')) ?: null,
            'statut' => (string) $request->query->get('statut') ?: null,
            'filtre' => (string) $request->query->get('filtre') ?: null,
            'du' => $date('du'),
            'au' => $date('au'),
            'livraison' => (string) $request->query->get('livraison') ?: null,
        ];
    }
}
