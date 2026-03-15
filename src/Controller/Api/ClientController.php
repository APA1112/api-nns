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
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

#[OA\Tag(name: 'Clients')]
final class ClientController extends AbstractController
{
    // Endpoint GET para obtener los clientes
    #[Route('/api/clients', name: 'api_clients_index', methods: ["GET"])]
    #[OA\Get(
        summary: 'Obtener lista de clientes',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de clientes retornada con éxito',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Client::class, groups: ['client:read']))
                )
            )
        ]
    )]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $clients = $entityManager->getRepository(Client::class)->findAll();

        return $this->json($clients, 200, [], ['groups' => 'client:read']);
    }

    // Endpoint GET para obtener un cliente por su ID
    #[Route('/api/clients/{id}', name: 'api_clients_detail', methods: ["GET"])]
        #[OA\Get(
        summary: 'Obtener cliente filtrando por id',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cliente retornado con éxito',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Client::class, groups: ['client:read']))
                )
            )
        ]
    )]
    public function detail(Client $client): JsonResponse
    {
        return $this->json($client, 200, [], ['groups' => 'client:read', 'ignored_attributes' => ['id']]);
    }

    // Endpoint POST para crear un nuevo cliente
    #[Route('/api/clients', name: 'api_clients_create', methods: ["POST"])]
    #[OA\Post(
        summary: 'Registrar un nuevo cliente',
        description: 'Crea un cliente en el sistema. El DNI debe ser válido (8 números y letra) y el teléfono debe tener formato internacional.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: Client::class, groups: ['client:write']),
                example: [
                    "dni" => "12345678Z",
                    "fullName" => "Juan Pérez García",
                    "address" => "Calle Mayor 15, Lucena, Córdoba",
                    "phone" => "+34 600123456",
                    "createdAt" => "2025-03-07 00:49:44"
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Cliente creado exitosamente',
                content: new OA\JsonContent(ref: new Model(type: Client::class, groups: ['client:read']))
            ),
            new OA\Response(
                response: 400,
                description: 'Error de validación en los datos enviados',
                content: new OA\JsonContent(
                    example: [
                        "dni" => ["Este valor no es un DNI válido."],
                        "fullName" => ["Este valor es demasiado corto. Debe tener 3 caracteres o más."]
                    ]
                )
            )
        ]
    )]
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

    // Endpoint UPDATE para actualizar un cliente por su ID
    #[Route('/api/clients/{id}', name: 'api_clients_update', methods: ["PUT", "PATCH"])]
    #[OA\Put(
        summary: 'Actualizar un cliente existente',
        description: 'Actualiza los datos de un cliente. Puedes enviar todos los campos (PUT) o solo algunos (PATCH).',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: Client::class, groups: ['client:write']),
                example: [
                    "fullName" => "Juan Pérez Modificado",
                    "address" => "Nueva Dirección, Lucena",
                    "phone" => "+34 666777888"
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cliente actualizado con éxito',
                content: new OA\JsonContent(ref: new Model(type: Client::class, groups: ['client:read']))
            ),
            new OA\Response(
                response: 400,
                description: 'Datos inválidos'
            ),
            new OA\Response(
                response: 404,
                description: 'Cliente no encontrado'
            )
        ]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID del cliente a actualizar',
        schema: new OA\Schema(type: 'integer')
    )]
    public function update(Client $client, Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $serializer->deserialize($request->getContent(), Client::class, 'json', ['object_to_populate' => $client]);

        $entityManager->flush();

        return $this->json($client, 200, [], ['groups' => 'client:read', 'ignored_attributes' => ['id']]);
    }

    // Endpoint DELETE para eliminar un cliente por su ID
    #[Route('/api/clients/{id}', name: 'api_clients_delete', methods: ["DELETE"])]
    #[OA\Delete(
        summary: 'Eliminar un cliente',
        responses: [
            new OA\Response(response: 204, description: 'Cliente eliminado'),
            new OA\Response(response: 409, description: 'Conflicto: Cliente con servicios activos'),
            new OA\Response(response: 400, description: 'Error de integridad')
        ]
    )]
    public function delete(
        Client $client,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            // Opcional: Validación de negocio
            if (!$client->getServices()->isEmpty()) {
                return $this->json([
                    'error' => 'No se puede eliminar un cliente con servicios activos.'
                ], Response::HTTP_CONFLICT); // 409 Conflict
            }

            $entityManager->remove($client);
            $entityManager->flush();

            // 204 No Content es el estándar correcto para DELETE exitoso
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (ForeignKeyConstraintViolationException $e) {
            return $this->json([
                'error' => 'Error de integridad: el cliente está siendo utilizado en otra tabla.'
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'No se pudo eliminar el cliente en este momento.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
