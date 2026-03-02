<?php

namespace App\Controller\Api;

use App\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

final class ClientController extends AbstractController
{
    #[Route('/api/clients', name: 'api_clients_index', methods: ["GET"])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $clients = $entityManager->getRepository(Client::class)->findOneBy(['id' => 63]);
        return $this->json($clients, 200, [], ['groups' => 'client:read']);
    }

    #[Route('/api/clients', name: 'api_clients_create', methods: ["POST"])]
    public function create(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): JsonResponse
    {
        $content = $request->getContent();
        $client = $serializer->deserialize($content, Client::class, 'json');
        $entityManager->persist($client);
        $entityManager->flush();
        return $this->json($client, 201);
    }
}
