<?php

namespace App\Factory;

use App\Entity\ServiceFtth;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ServiceFtth>
 */
final class ServiceFtthFactory extends PersistentProxyObjectFactory
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
        return ServiceFtth::class;
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
            // Campos de la clase PADRE
            'client' => ClientFactory::new(),
            'installAddress' => self::faker()->address(),
            'status' => self::faker()->randomElement(['Activo', 'Suspendido', 'Baja']),
            'type' => 'FTTH',
            // Campos de la clase HIJA
            'ontMac' => self::faker()->macAddress(),
            'ponPort' => self::faker()->bothify('PON-##'),
            'splitterId' => self::faker()->bothify('SPL-###'),
            'opticalPower' => self::faker()->randomFloat(2, -28, -15),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(ServiceFtth $serviceFtth): void {})
        ;
    }
}
