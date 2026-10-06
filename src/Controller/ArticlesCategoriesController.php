<?php

namespace App\Controller;

use App\Entity\ArticlesCategories;
use App\Form\ArticlesCategoriesType;
use App\Repository\ArticlesCategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/articles/categories')]
class ArticlesCategoriesController extends AbstractController
{
    #[Route('/', name: 'app_articles_categories_index', methods: ['GET','POST'])]
    public function index(ArticlesCategoriesRepository $articlesCategoriesRepository,Request $request, EntityManagerInterface $entityManager): Response
    {

        $articlesCategory = new ArticlesCategories();
        $form = $this->createForm(ArticlesCategoriesType::class, $articlesCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($articlesCategory);
            $entityManager->flush();

            return $this->redirectToRoute('app_articles_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('articles_categories/index.html.twig', [
            'articles_categories' => $articlesCategoriesRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_articles_categories_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $articlesCategory = new ArticlesCategories();
        $form = $this->createForm(ArticlesCategoriesType::class, $articlesCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($articlesCategory);
            $entityManager->flush();

            return $this->redirectToRoute('app_articles_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('articles_categories/new.html.twig', [
            'articles_category' => $articlesCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_articles_categories_show', methods: ['GET'])]
    public function show(ArticlesCategories $articlesCategory): Response
    {
        return $this->render('articles_categories/show.html.twig', [
            'articles_category' => $articlesCategory,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_articles_categories_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ArticlesCategories $articlesCategory, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticlesCategoriesType::class, $articlesCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_articles_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('articles_categories/edit.html.twig', [
            'articles_category' => $articlesCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_articles_categories_delete', methods: ['POST'])]
    public function delete(Request $request, ArticlesCategories $articlesCategory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$articlesCategory->getId(), $request->request->get('_token'))) {
            $entityManager->remove($articlesCategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_articles_categories_index', [], Response::HTTP_SEE_OTHER);
    }
}
