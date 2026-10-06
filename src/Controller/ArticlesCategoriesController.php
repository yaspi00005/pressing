<?php

namespace App\Controller;

use App\Entity\ArticlesCategories;
use App\Form\ArticlesCategoriesType;
use App\Repository\ArticlesCategoriesRepository;
use App\Repository\LigneCommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/articles/categories')]
class ArticlesCategoriesController extends AbstractController
{
    #[Route('/', name: 'app_articles_categories_index', methods: ['GET', 'POST'])]
    public function index(ArticlesCategoriesRepository $repo, Request $request, EntityManagerInterface $em): Response
    {
        $categorie = new ArticlesCategories();
        $form = $this->createForm(ArticlesCategoriesType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie);
            $em->flush();
            $this->addFlash('success', 'Catégorie « '.$categorie->getNoms().' » ajoutée.');

            return $this->redirectToRoute('app_articles_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('articles_categories/index.html.twig', [
            'articles_categories' => $repo->findBy([], ['noms' => 'ASC']),
            'form' => $form->createView(),
            'ouvrir_modal' => $form->isSubmitted(),
        ]);
    }

    #[Route('/{url}/edit', name: 'app_articles_categories_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, #[MapEntity(mapping: ['url' => 'url'])] ArticlesCategories $articlesCategory, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ArticlesCategoriesType::class, $articlesCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Catégorie mise à jour.');

            return $this->redirectToRoute('app_articles_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('articles_categories/edit.html.twig', [
            'articles_category' => $articlesCategory,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{url}/delete', name: 'app_articles_categories_delete', methods: ['POST'])]
    public function delete(Request $request, #[MapEntity(mapping: ['url' => 'url'])] ArticlesCategories $articlesCategory, EntityManagerInterface $em, LigneCommandeRepository $lignes): Response
    {
        if ($this->isCsrfTokenValid('delete'.$articlesCategory->getId(), (string) $request->request->get('_token'))) {
            foreach ($articlesCategory->getArticlesSousCategories() as $article) {
                if ($lignes->count(['article' => $article]) > 0) {
                    $this->addFlash('warning', 'Suppression impossible : « '.$article->getNoms().' » figure dans des commandes.');

                    return $this->redirectToRoute('app_articles_categories_index', [], Response::HTTP_SEE_OTHER);
                }
                foreach ($em->getRepository(\App\Entity\Tarif::class)->findBy(['article' => $article]) as $tarif) {
                    $em->remove($tarif);
                }
            }
            $em->remove($articlesCategory);
            $em->flush();
            $this->addFlash('success', 'Catégorie supprimée.');
        }

        return $this->redirectToRoute('app_articles_categories_index', [], Response::HTTP_SEE_OTHER);
    }
}
