<?php

namespace App\Controller;

use App\Entity\ArticlesCategories;
use App\Entity\ArticlesSousCategorie;
use App\Entity\Tarif;
use App\Form\ArticlesSousCategorieType;
use App\Repository\ArticlesSousCategorieRepository;
use App\Repository\LigneCommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/articles/sous/categorie')]
class ArticlesSousCategorieController extends AbstractController
{
    /** Tous les articles, toutes catégories confondues. */
    #[Route('/', name: 'app_articles_sous_categorie_index', methods: ['GET', 'POST'])]
    public function index(ArticlesSousCategorieRepository $repo, Request $request, EntityManagerInterface $em): Response
    {
        $article = new ArticlesSousCategorie();
        $form = $this->createForm(ArticlesSousCategorieType::class, $article, ['avec_categorie' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($article);
            $em->flush();
            $this->addFlash('success', 'Article « '.$article->getNoms().' » ajouté.');

            return $this->redirectToRoute('app_articles_sous_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('articles_sous_categorie/index.html.twig', [
            'categorie' => null,
            'articles_sous_categories' => $repo->findBy([], ['noms' => 'ASC']),
            'form' => $form->createView(),
            'ouvrir_modal' => $form->isSubmitted(),
        ]);
    }

    /** Articles d'une catégorie (adresse opaque : /{url}). */
    #[Route('/{url}', name: 'app_articles_sous_categorie_show', methods: ['GET', 'POST'])]
    public function show(#[MapEntity(mapping: ['url' => 'url'])] ArticlesCategories $articlesCategorie, ArticlesSousCategorieRepository $repo, Request $request, EntityManagerInterface $em): Response
    {
        $article = new ArticlesSousCategorie();
        $form = $this->createForm(ArticlesSousCategorieType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setCategorie($articlesCategorie);
            $em->persist($article);
            $em->flush();
            $this->addFlash('success', 'Article « '.$article->getNoms().' » ajouté.');

            return $this->redirectToRoute('app_articles_sous_categorie_show', ['url' => $articlesCategorie->getUrl()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('articles_sous_categorie/index.html.twig', [
            'categorie' => $articlesCategorie,
            'articles_sous_categories' => $repo->findBy(['categorie' => $articlesCategorie], ['noms' => 'ASC']),
            'form' => $form->createView(),
            'ouvrir_modal' => $form->isSubmitted(),
        ]);
    }

    #[Route('/article/{url}/edit', name: 'app_articles_sous_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, #[MapEntity(mapping: ['url' => 'url'])] ArticlesSousCategorie $articlesSousCategorie, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ArticlesSousCategorieType::class, $articlesSousCategorie, ['avec_categorie' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Article mis à jour.');

            return $this->redirectToRoute('app_articles_sous_categorie_show', ['url' => $articlesSousCategorie->getCategorie()->getUrl()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('articles_sous_categorie/edit.html.twig', [
            'articles_sous_categorie' => $articlesSousCategorie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/{url}/delete', name: 'app_articles_sous_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, #[MapEntity(mapping: ['url' => 'url'])] ArticlesSousCategorie $articlesSousCategorie, EntityManagerInterface $em, LigneCommandeRepository $lignes): Response
    {
        $retour = $this->redirectToRoute('app_articles_sous_categorie_show', ['url' => $articlesSousCategorie->getCategorie()->getUrl()], Response::HTTP_SEE_OTHER);

        if ($this->isCsrfTokenValid('delete'.$articlesSousCategorie->getId(), (string) $request->request->get('_token'))) {
            if ($lignes->count(['article' => $articlesSousCategorie]) > 0) {
                $this->addFlash('warning', 'Suppression impossible : cet article figure dans des commandes.');

                return $retour;
            }
            foreach ($em->getRepository(Tarif::class)->findBy(['article' => $articlesSousCategorie]) as $tarif) {
                $em->remove($tarif);
            }
            $em->remove($articlesSousCategorie);
            $em->flush();
            $this->addFlash('success', 'Article supprimé.');
        }

        return $retour;
    }
}
