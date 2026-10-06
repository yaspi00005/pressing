<?php

namespace App\Controller;

use App\Repository\ArticlesCategoriesRepository;
use App\Repository\ArticlesSousCategorieRepository;
use App\Repository\ClientsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        ClientsRepository $clients,
        ArticlesCategoriesRepository $categories,
        ArticlesSousCategorieRepository $articles
    ): Response {
        return $this->render('dashboard/index.html.twig', [
            'nb_clients' => $clients->count([]),
            'nb_categories' => $categories->count([]),
            'nb_articles' => $articles->count([]),
        ]);
    }
}
