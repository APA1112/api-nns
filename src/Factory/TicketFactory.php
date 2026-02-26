<?php

namespace App\Factory;

use App\Entity\Ticket;
use App\Entity\Service;
use App\Repository\ServiceRepository;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Ticket>
 */
final class TicketFactory extends PersistentProxyObjectFactory
{
    private ServiceRepository $serviceRepository;

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(ServiceRepository $serviceRepository)
    {
        parent::__construct();
        $this->serviceRepository = $serviceRepository;
    }

    #[\Override]
    public static function class(): string
    {
        return Ticket::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'service' => $this->getRandomService(),
            'creator' => UserFactory::new(),
            'assignedRole' => RoleFactory::random(),
            'priority' => self::faker()->randomElement(['BAJA', 'MEDIA', 'ALTA']),
            'status' => self::faker()->randomElement(['ABIERTO', 'EN CURSO', 'CERRADO', 'BLOQUEADO']),
            'subject' => self::faker()->sentence(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    public function getRandomService(): ?Service
    {
        $services = $this->serviceRepository->findAll();
        if (empty($services)) {
            return null; // No hay servicios
        }

        return self::faker()->randomElement($services); // Devuelve un servicio aleatorio
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Ticket $ticket): void {
                // Generamos el comentario inicial obligatorio
                TicketCommentFactory::createOne([
                    'ticket' => $ticket,
                    'CreatorUser' => $ticket->getCreator(), // El autor es el mismo que creó el ticket
                    'comment' => 'Descripción inicial del problema: ' . self::faker()->paragraph(),
                    'createdAt' => $ticket->getCreatedAt(),
                ]);
            });
    }
}
