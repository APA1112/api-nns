<?php

namespace App\Factory;

use App\Entity\ServiceWimax;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ServiceWimax>
 */
final class ServiceWimaxFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct() {}

    #[\Override]
    public static function class(): string
    {
        return ServiceWimax::class;
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
            // Campos de la clase PADRE (Service)
            'client' => ClientFactory::new(),
            'installAddress' => self::faker()->address(),
            'status' => self::faker()->randomElement(['Activo', 'Suspendido', 'Baja']),
            'type' => 'WIMAX',
            // Campos de la clase HIJA (ServiceWimax)
            'antennaIp' => self::faker()->ipv4(),
            'antennaMac' => self::faker()->macAddress(),
            'apName' => 'AP-' . self::faker()->city(),
            'signalStrength' => self::faker()->numberBetween(-40, -80),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(ServiceWimax $serviceWimax): void {})
        ;
    }
}
