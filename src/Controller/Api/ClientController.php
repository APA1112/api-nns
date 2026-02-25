<?php

namespace App\Controller\Api;

use App\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

final class ClientController extends AbstractController
{
    #[Route('/api/clients', name: 'api_clients_index', methods: ["GET"])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $clients = $entityManager->getRepository(Client::class)->findOneBy(['id' => 63]);
        return $this->json($clients, 200, [], ['groups' => 'client:read']);
    }
}
