<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GestionsClientsController extends AbstractController
{
    #[Route('/gestions/clients', name: 'app_gestions_clients')]
    public function index(): Response
    {
        return $this->render('gestions_clients/index.html.twig', [
            'controller_name' => 'GestionsClientsController',
        ]);
    }
}
