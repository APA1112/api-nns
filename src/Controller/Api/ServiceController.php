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
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

#[OA\Tag(name: "Services")]
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
    #[OA\Post(
        summary: 'Crear un nuevo servicio para un cliente',
        description: 'Crea un servicio asociado a un cliente existente. El estado inicial será "Pendiente de Instalación".',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                oneOf: [
                    new OA\Schema(ref: new Model(type: ServiceWimax::class, groups: ['service:write'])),
                    new OA\Schema(ref: new Model(type: ServiceFtth::class, groups: ['service:write']))
                ],
                example: [
                    "type" => "wimax",
                    "installAddress" => "Calle Falsa 123, Lucena",
                    "antennaMac" => "00:1B:44:11:3A:B7"
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Servicio creado con éxito',
                content: new OA\JsonContent(ref: new Model(type: Service::class, groups: ['service:read']))
            ),
            new OA\Response(
                response: 400,
                description: 'Tipo de servicio inválido o datos mal formados'
            ),
            new OA\Response(
                response: 404,
                description: 'El cliente especificado no existe'
            )
        ]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID del cliente al que se le asignará el servicio',
        schema: new OA\Schema(type: 'integer')
    )]
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
    #[OA\Put(
        summary: 'Actualizar un servicio existente',
        description: 'Actualiza los datos de un servicio. Si se proporcionan los campos técnicos del tipo correspondiente (ontMac para FTTH, antennaMac para WiMAX), el estado pasa a "Activo" automáticamente.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                oneOf: [
                    new OA\Schema(ref: new Model(type: ServiceWimax::class, groups: ['service:write'])),
                    new OA\Schema(ref: new Model(type: ServiceFtth::class, groups: ['service:write']))
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Servicio actualizado con éxito',
                content: new OA\JsonContent(ref: new Model(type: Service::class, groups: ['service:read']))
            ),
            new OA\Response(response: 400, description: 'Datos inválidos para el tipo de servicio'),
            new OA\Response(response: 404, description: 'Servicio no encontrado')
        ]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID del servicio a actualizar',
        schema: new OA\Schema(type: 'integer')
    )]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        DenormalizerInterface $denormalizer
    ): JsonResponse {
        // 1. Doctrine resuelve automáticamente la subclase correcta gracias a JOINED inheritance
        $service = $entityManager->getRepository(Service::class)->find($id);

        if (!$service) {
            return $this->json(['error' => 'Servicio no encontrado'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // 2. Validar que no se estén enviando campos del tipo incorrecto
        $validationError = $this->validateFieldsMatchServiceType($service, $data);
        if ($validationError) {
            return $this->json(['error' => $validationError], 400);
        }

        // 3. Denormalizar sobre el objeto existente (get_class devuelve ServiceFtth o ServiceWimax)
        $denormalizer->denormalize($data, get_class($service), null, [
            'groups'                          => ['service:write'],
            AbstractNormalizer::OBJECT_TO_POPULATE => $service,
        ]);

        // 4. Cambiar estado a "Activo" si se han completado los campos técnicos del tipo
        $this->updateStatusIfComplete($service);

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

    /**
     * Devuelve un mensaje de error si el payload contiene campos
     * que no corresponden al tipo real del servicio, o null si todo es correcto.
     */
    private function validateFieldsMatchServiceType(Service $service, array $data): ?string
    {
        $ftthOnlyFields  = ['ontMac', 'ponPort', 'splitterId', 'opticalPower'];
        $wimaxOnlyFields = ['antennaMac', 'antennaIp', 'apName', 'signalStrength'];

        if ($service instanceof ServiceFtth) {
            $forbidden = array_intersect(array_keys($data), $wimaxOnlyFields);
            if (!empty($forbidden)) {
                return sprintf(
                    'El servicio es de tipo FTTH. Campos no permitidos: %s',
                    implode(', ', $forbidden)
                );
            }
        }

        if ($service instanceof ServiceWimax) {
            $forbidden = array_intersect(array_keys($data), $ftthOnlyFields);
            if (!empty($forbidden)) {
                return sprintf(
                    'El servicio es de tipo WiMAX. Campos no permitidos: %s',
                    implode(', ', $forbidden)
                );
            }
        }

        return null;
    }

    /**
     * Cambia el estado a "Activo" cuando el campo técnico principal
     * del tipo correspondiente ya está relleno.
     */
    private function updateStatusIfComplete(Service $service): void
    {
        if ($service instanceof ServiceFtth && $service->getOntMac() !== null && $service->getStatus() === 'Pendiente de Instalación') {
            $service->setStatus('Activo');
            return;
        }

        if ($service instanceof ServiceWimax && $service->getAntennaMac() !== null && $service->getStatus() === 'Pendiente de Instalación') {
            $service->setStatus('Activo');
            return;
        }
    }
}
