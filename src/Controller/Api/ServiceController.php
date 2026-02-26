<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Service;

final class ServiceController extends AbstractController
{
    #[Route('/api/services', name: 'app_api_service', methods: ["GET"])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $services = $entityManager->getRepository(Service::class)->findAll();
        return $this->json($services, 200, [], ['groups' => 'service:read']);
    }
}
