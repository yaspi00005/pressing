<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GestionsArticlesController extends AbstractController
{
    #[Route('/gestions/articles', name: 'app_gestions_articles')]
    public function index(): Response
    {
        return $this->render('gestions_articles/index.html.twig', [
            'controller_name' => 'GestionsArticlesController',
        ]);
    }
}
