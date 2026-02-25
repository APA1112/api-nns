<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServiceController extends AbstractController
{
    #[Route('/api/service', name: 'app_api_service')]
    public function index(): Response
    {
        return $this->render('api/service/index.html.twig', [
            'controller_name' => 'ServiceController',
        ]);
    }
}
