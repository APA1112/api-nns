<?php

namespace App\Controller\Api;

use App\Entity\Ticket;
use App\Entity\TicketComment;
use App\Entity\Service;
use App\Entity\User;
use App\Entity\Role;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

#[OA\Tag(name: "Tickets")]
final class TicketController extends AbstractController
{
    #[Route('/api/tickets', name: 'api_tickets_index', methods: ["GET"])]
    #[OA\Post(
        summary: 'Crear ticket con comentario inicial',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['subject', 'priority', 'service_id', 'creator_id', 'assigned_role_id'],
                properties: [
                    new OA\Property(property: 'subject', type: 'string'),
                    new OA\Property(property: 'priority', type: 'string', example: 'ALTA'),
                    new OA\Property(property: 'service_id', type: 'integer'),
                    new OA\Property(property: 'creator_id', type: 'integer'),
                    new OA\Property(property: 'assigned_role_id', type: 'integer'),
                    new OA\Property(property: 'description', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Ticket generado')
        ]
    )]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $tickets = $entityManager->getRepository(Ticket::class)->findAll();
        return $this->json($tickets, 200, [], ['groups' => 'ticket:read', 'service:read']);
    }
    // CASO BUZÓN (Tickets activos)
    #[Route('/api/tickets/mailbox', name: 'tickets_mailbox', methods: ['GET'])]
    #[OA\Get(
        summary: 'Buzón de tickets activos',
        responses: [
            new OA\Response(
                response: 200, 
                description: 'Tickets filtrados por estado activo',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: Ticket::class, groups: ['ticket:read', 'service:read'])))
            )
        ]
    )]
    public function listMailbox(TicketRepository $ticketRepository): JsonResponse
    {
        $tickets = $ticketRepository->findForMailbox();

        return $this->json($tickets, 200, [], [
            'groups' => 'ticket:read'
        ]);
    }
    // CASO POR SERVICIO (Todos los tickets de un servicio)
    #[Route('/api/services/{id}/tickets', name: 'tickets_by_service', methods: ['GET'])]
    public function listByService(Service $service, TicketRepository $ticketRepository): JsonResponse
    {
        // Symfony hace el ParamConverter automático con el {id} para obtener el objeto Service
        $tickets = $ticketRepository->findByService($service);

        return $this->json($tickets, 200, [], [
            'groups' => 'ticket:read'
        ]);
    }

    // Crear un nuevo ticket con un comentario inicial
    #[Route('/api/tickets', name: 'create_ticket', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $ticket = new Ticket();
        $ticket->setSubject($data['subject']);
        $ticket->setPriority(strtoupper($data['priority']));
        $ticket->setStatus('ABIERTO');
        $ticket->setCreatedAt(new \DateTimeImmutable());

        // Asignar Servicio
        $service = $em->getRepository(Service::class)->find($data['service_id']);
        $ticket->setService($service);

        // Asignar Creador (Manualmente por ahora)
        $user = $em->getRepository(User::class)->find($data['creator_id']);
        $ticket->setCreator($user);

        // Asignar Rol
        if (!isset($data['assigned_role_id'])) {
            return $this->json(['error' => 'Debes asignar un rol al ticket'], 400);
        }

        $role = $em->getRepository(Role::class)->find($data['assigned_role_id']);

        if (!$role) {
            return $this->json(['error' => 'El rol especificado no existe'], 404);
        }

        $ticket->setAssignedRole($role);

        // Crear el TicketComment inicial
        // Usamos el 'subject' o una descripción si la envías en el JSON
        $initialComment = new TicketComment();
        $initialComment->setComment("Comentario inicial: " . ($data['description'] ?? $data['subject']));
        $initialComment->setCreatedAt(new \DateTimeImmutable());
        $initialComment->setCreatorUser($user); // El creador del ticket es el autor del primer comentario

        // Vincular comentario con ticket
        $ticket->addTicketComment($initialComment);

        $em->persist($ticket);
        $em->flush();

        return $this->json($ticket, 201, [], ['groups' => 'ticket:read']);
    }

    // Editamos un ticket ya existente
    #[Route('/api/ticket/{id}', name: 'ticket_edit', methods: ['PATCH', 'PUT'])]
    public function update(Ticket $ticket, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Cambiar estado si viene en la petición
        if (isset($data['status'])) {
            $ticket->setStatus($data['status']);
        }

        // Añadir comentario si viene en la petición
        if (isset($data['comment'])) {
            // Buscamos al usuario manualmente del JSON porque no hay sesión activa
            $userId = $data['creator_id'] ?? null;
            $user = $userId ? $em->getRepository(User::class)->find($userId) : null;

            if (!$user) {
                return $this->json(['error' => 'Se requiere un ID de usuario válido (creator_id) para comentar'], 400);
            }
            $comment = new TicketComment();
            $comment->setComment($data['comment']);
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setCreatorUser($user);

            $ticket->addTicketComment($comment);
            $em->persist($comment);
        }

        $em->flush();

        return $this->json($ticket, 200, [], ['groups' => 'ticket:read']);
    }

    // Borrado suave para mantener la constancia historica
    #[Route('/api/ticket/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Ticket $ticket, EntityManagerInterface $em): JsonResponse
    {
        // En lugar de $em->remove($ticket), simplemente cambiamos su estado
        $ticket->setStatus('CERRADO');
        $em->flush();

        return $this->json(['message' => 'Ticket cerrado correctamente']);
    }
}
