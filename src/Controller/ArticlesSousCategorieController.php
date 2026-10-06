<?php

namespace App\Controller;

use App\Entity\ArticlesCategories;
use App\Entity\ArticlesSousCategorie;
use App\Form\ArticlesSousCategorieType;
use App\Repository\ArticlesSousCategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/articles/sous/categorie')]
class ArticlesSousCategorieController extends AbstractController
{
    #[Route('/', name: 'app_articles_sous_categorie_index', methods: ['GET'])]
    public function index(ArticlesSousCategorieRepository $articlesSousCategorieRepository): Response
    {
        return $this->render('articles_sous_categorie/index.html.twig', [
            'articles_sous_categories' => $articlesSousCategorieRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_articles_sous_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $articlesSousCategorie = new ArticlesSousCategorie();
        $form = $this->createForm(ArticlesSousCategorieType::class, $articlesSousCategorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($articlesSousCategorie);
            $entityManager->flush();

            return $this->redirectToRoute('app_articles_sous_categorie_new', []);
        }

        return $this->renderForm('articles_sous_categorie/new.html.twig', [
            'articles_sous_categorie' => $articlesSousCategorie,
            'form' => $form,
        ]);
    }

    #[Route('/{url}', name: 'app_articles_sous_categorie_show', methods: ['GET','POST'])]
    public function show(ArticlesCategories $articlesCategorie,ArticlesSousCategorieRepository $articlesSousCategorieRepo,Request $request, EntityManagerInterface $entityManager): Response
    {

        $articlesSousCategorie = new ArticlesSousCategorie();
        $form = $this->createForm(ArticlesSousCategorieType::class, $articlesSousCategorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articlesSousCategorie->setCategorie($articlesCategorie);
            $entityManager->persist($articlesSousCategorie);
            $entityManager->flush();


            return $this->redirectToRoute('app_articles_sous_categorie_show', ['url'=> $articlesCategorie->getUrl()], Response::HTTP_SEE_OTHER);

        }

        $souscat = $articlesSousCategorieRepo->findBy(['categorie'=> $articlesCategorie]);

        return $this->render('articles_sous_categorie/index.html.twig', [
            'articles_sous_categories' => $souscat,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{url}/edit', name: 'app_articles_sous_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ArticlesSousCategorie $articlesSousCategorie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticlesSousCategorieType::class, $articlesSousCategorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_articles_sous_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('articles_sous_categorie/edit.html.twig', [
            'articles_sous_categorie' => $articlesSousCategorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_articles_sous_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, ArticlesSousCategorie $articlesSousCategorie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$articlesSousCategorie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($articlesSousCategorie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_articles_sous_categorie_index', [], Response::HTTP_SEE_OTHER);
    }
}
