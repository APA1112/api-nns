<?php

namespace App\Repository;

use App\Entity\Ticket;
use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }
    public function findForMailbox(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.ticketComments', 'c')->addSelect('c') // Carga comentarios
            ->leftJoin('t.service', 's')->addSelect('s')      // Carga el servicio
            ->where('t.status IN (:statuses)')
            ->setParameter('statuses', ['ABIERTO', 'EN CURSO', 'BLOQUEADO'])
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByService(Service $service): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.ticketComments', 'c')->addSelect('c')
            ->where('t.service = :service')
            ->setParameter('service', $service)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
