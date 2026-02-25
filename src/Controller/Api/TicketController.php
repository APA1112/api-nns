<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TicketController extends AbstractController
{
    #[Route('/api/ticket', name: 'app_api_ticket')]
    public function index(): Response
    {
        return $this->render('api/ticket/index.html.twig', [
            'controller_name' => 'TicketController',
        ]);
    }
}
