<?php

namespace App\Controller\Api;

use App\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ClientController extends AbstractController
{
    #[Route('/api/clients', name: 'api_clients_index', methods: ["GET"])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $clients = $entityManager->getRepository(Client::class)->findOneBy(['id' => 63]);
        return $this->json($clients, 200, [], ['groups' => 'client:read']);
    }

    #[Route('/api/clients', name: 'api_clients_create', methods: ["POST"])]
    public function create(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $content = $request->getContent();
        $client = $serializer->deserialize($content, Client::class, 'json');
        $errors = $validator->validate($client);
        if (count($errors) > 0) {
            $error_messages = [];
            foreach ($errors as $error) {
                $error_messages[$error->getPropertyPath()][] = $error->getMessage();
            }  
            return $this->json($error_messages, 400);
        }
        $entityManager->persist($client);
        $entityManager->flush();
        return $this->json($client, 201);
    }
}
