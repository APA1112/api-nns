<?php

namespace App\Controller\Api;

use App\Entity\Ticket;
use App\Entity\TicketComment;
use App\Entity\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\Request;

final class TicketController extends AbstractController
{
    #[Route('/api/tickets', name: 'api_tickets_index', methods: ["GET"])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $tickets = $entityManager->getRepository(Ticket::class)->findAll();
        return $this->json($tickets, 200, [], ['groups' => 'ticket:read']);
    }
    // CASO BUZÓN (Tickets activos)
    #[Route('/api/mailbox', name: 'tickets_mailbox', methods: ['GET'])]
    public function mailbox(TicketRepository $ticketRepository): JsonResponse
    {
        $tickets = $ticketRepository->findForMailbox();

        return $this->json($tickets, 200, [], [
            'groups' => 'ticket:read'
        ]);
    }
    // CASO POR SERVICIO (Todos los tickets de un servicio)
    #[Route('/api/service/{id}', name: 'tickets_by_service', methods: ['GET'])]
    public function byService(Service $service, TicketRepository $ticketRepository): JsonResponse
    {
        // Symfony hace el ParamConverter automático con el {id} para obtener el objeto Service
        $tickets = $ticketRepository->findByService($service);

        return $this->json($tickets, 200, [], [
            'groups' => 'ticket:read'
        ]);
    }

    // Crear un nuevo ticket con un comentario inicial
    #[Route('/api/ticket/new', name:'create_ticket', methods:['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $ticket = new Ticket();
        $ticket->setSubject($data['subject']);
        $ticket->setPriority($data['priority']);
        $ticket->setStatus('abierto'); // Estado inicial por defecto
        $ticket->setCreatedAt(new \DateTimeImmutable());

        // Asignamos el servicio (puedes usar un Repository para buscarlo por ID)
        $service = $em->getRepository(Service::class)->find($data['service_id']);
        $ticket->setService($service);

        // El creador suele ser el usuario autenticado
        $ticket->setCreator($this->getUser());

        $em->persist($ticket);
        $em->flush();

        return $this->json($ticket, 201, [], ['groups' => 'ticket:read']);
    }

    // Editamos un ticket ya existente
    #[Route('/api/ticket//{id}', name: 'edit', methods: ['PATCH', 'PUT'])]
    public function edit(Ticket $ticket, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // 1. Cambiar estado si viene en la petición
        if (isset($data['status'])) {
            $ticket->setStatus($data['status']);
        }

        // 2. Añadir comentario si viene en la petición
        if (isset($data['comment'])) {
            $comment = new TicketComment();
            $comment->setComment($data['comment']);
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setCreatorUser($this->getUser());

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
