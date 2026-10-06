<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\Tarif;
use App\Form\ServiceType;
use App\Repository\ArticlesSousCategorieRepository;
use App\Repository\ServiceRepository;
use App\Repository\TarifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tarifs')]
class TarifController extends AbstractController
{
    /** Grille de prix : une ligne par article, une colonne par service. */
    #[Route('/', name: 'app_tarif_index', methods: ['GET', 'POST'])]
    public function index(Request $request, ArticlesSousCategorieRepository $articles, ServiceRepository $services, TarifRepository $tarifs, EntityManagerInterface $em): Response
    {
        $listeServices = $services->findBy([], ['nom' => 'ASC']);
        $listeArticles = $articles->findBy([], ['noms' => 'ASC']);

        $existants = [];
        foreach ($tarifs->findAll() as $t) {
            $existants[$t->getArticle()->getId()][$t->getService()->getId()] = $t;
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('tarifs', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException();
            }
            $saisie = $request->request->all('prix');
            foreach ($listeArticles as $a) {
                foreach ($listeServices as $s) {
                    $valeur = $saisie[$a->getId()][$s->getId()] ?? '';
                    $tarif = $existants[$a->getId()][$s->getId()] ?? null;
                    if ('' === $valeur || !is_numeric($valeur) || (int) $valeur < 0) {
                        if ($tarif) {
                            $em->remove($tarif);
                        }
                        continue;
                    }
                    if (!$tarif) {
                        $tarif = (new Tarif())->setArticle($a)->setService($s);
                        $em->persist($tarif);
                    }
                    $tarif->setPrix((int) $valeur);
                }
            }
            $em->flush();
            $this->addFlash('success', 'Grille tarifaire enregistrée.');

            return $this->redirectToRoute('app_tarif_index', [], Response::HTTP_SEE_OTHER);
        }

        $service = new Service();
        $service->setActif(true);
        $form = $this->createForm(ServiceType::class, $service, ['action' => $this->generateUrl('app_service_new')]);

        return $this->render('tarif/index.html.twig', [
            'articles' => $listeArticles,
            'services' => $listeServices,
            'tarifs' => $existants,
            'form' => $form,
        ]);
    }

    #[Route('/services/new', name: 'app_service_new', methods: ['POST'])]
    public function newService(Request $request, EntityManagerInterface $em): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($service);
            $em->flush();
            $this->addFlash('success', 'Service ajouté : renseignez maintenant ses prix.');
        } else {
            $this->addFlash('danger', 'Service invalide (nom et code uniques obligatoires).');
        }

        return $this->redirectToRoute('app_tarif_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/services/{id}/toggle', name: 'app_service_toggle', methods: ['POST'])]
    public function toggle(Request $request, Service $service, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('service'.$service->getId(), (string) $request->request->get('_token'))) {
            $service->setActif(!$service->isActif());
            $em->flush();
        }

        return $this->redirectToRoute('app_tarif_index', [], Response::HTTP_SEE_OTHER);
    }
}
