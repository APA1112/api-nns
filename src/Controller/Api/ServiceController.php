<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Service;
use App\Entity\ServiceWimax;
use App\Entity\ServiceFtth;
use App\Entity\Client;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

final class ServiceController extends AbstractController
{
    // Endpoint GET para obtener todos los servicios
    #[Route('/api/services', name: 'app_api_service', methods: ["GET"])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $services = $entityManager->getRepository(Service::class)->findAll();
        return $this->json($services, 200, [], ['groups' => 'service:read']);
    }

    // Endpoint POST para crear un nuevo servicio para un cliente específico
    #[Route('/api/clients/{id}/services', name: 'api_service_create', methods: ['POST'])]
    public function create(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        DenormalizerInterface $denormalizer
    ): JsonResponse {
        $client = $entityManager->getRepository(Client::class)->find($id);

        if (!$client) {
            return $this->json(['error' => 'Cliente no encontrado'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $type = $data['type'] ?? null; // 'wimax' o 'ftth'

        // 1. Decidir qué clase instanciar
        $serviceClass = match (strtolower($type)) {
            'wimax' => ServiceWimax::class,
            'ftth' => ServiceFtth::class,
            default => null,
        };

        if (!$serviceClass) {
            return $this->json(['error' => 'Tipo de servicio inválido'], 400);
        }

        // 2. Denormalizar el JSON al objeto de la clase elegida
        // Ignoramos el campo 'type' del JSON para que no choque con la lógica de Doctrine
        $service = $denormalizer->denormalize($data, $serviceClass, null, [
            'groups' => ['service:write']
        ]);

        // 3. Configurar estado inicial y relación
        $service->setClient($client);
        $service->setStatus('Pendiente de Instalación');

        $entityManager->persist($service);
        $entityManager->flush();

        return $this->json($service, 201, [], ['groups' => 'service:read']);
    }

    // Endpoint PATCH/PUT para actualizar un servicio existente
    #[Route('/api/services/{id}', name: 'api_service_update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager, DenormalizerInterface $denormalizer): JsonResponse
    {
        $service = $entityManager->getRepository(Service::class)->find($id);

        if (!$service) {
            return $this->json(['error' => 'Servicio no encontrado'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // Denormalizar los datos al objeto de servicio existente
        $denormalizer->denormalize($data, get_class($service), null, [
            'groups' => ['service:write'],
            AbstractNormalizer::OBJECT_TO_POPULATE => $service
        ]);

        // Actualizar el estado automáticamente si se han proporcionado campos de las entidades hijas
        if (isset($data['ontMac']) || isset($data['antennaMac'])) {
            $service->setStatus('Activo');
        }

        $entityManager->flush();

        return $this->json($service, 200, [], ['groups' => 'service:read']);
    }

    //Endpoint DELETE para eliminar un servicio por su ID
    #[Route('/api/services/{id}', name: 'api_service_delete', methods: ['DELETE'])]
    public function delete(Service $service, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$service) {
            return $this->json(['error' => 'Servicio no encontrado'], 404);
        }
        $service->setStatus('Baja');
        $entityManager->flush();
        return $this->json(['message' => 'Servicio dado de baja correctamente']);
    }
}
