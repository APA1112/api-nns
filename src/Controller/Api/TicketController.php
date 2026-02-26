<?php

namespace App\Controller\Api;

use App\Entity\Ticket;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

final class TicketController extends AbstractController
{
    #[Route('/api/tickets', name: 'api_tickets_index', methods: ["GET"])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $tickets = $entityManager->getRepository(Ticket::class)->findOneBy(['id' => 63]);
        return $this->json($tickets, 200, [], ['groups' => 'ticket:read']);
    }
}