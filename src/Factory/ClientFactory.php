<?php

namespace App\Factory;

use App\Entity\Client;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Client>
 */
final class ClientFactory extends PersistentProxyObjectFactory
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
        return Client::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
protected function defaults(): array|callable
    {
        // Forzamos el locale a español para direcciones y nombres realistas
        $faker = self::faker();
        $faker->locale('es_ES');

        return [
            'dni' => $this->generateValidDNI(),
            'fullName' => $faker->firstName() . ' ' . $faker->lastName() . ' ' . $faker->lastName(), // Generará algo como "Juan Pérez"
            'address' => $faker->address(), // Generará algo como "Calle Mayor 1, Madrid"
            'phone' => $faker->regexify('/[679][0-9]{8}/'), // Móviles o fijos españoles (9 dígitos)
            'createdAt' => \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-2 years', 'now')),
        ];
    }

    /**
     * Calcula un DNI con letra válida (Algoritmo oficial)
     */
    private function generateValidDNI(): string
    {
        $number = self::faker()->randomNumber(8, true);
        $letters = "TRWAGMYFPDXBNJZSQVHLCKE";
        $letter = $letters[$number % 23];

        return $number . $letter;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Client $client): void {})
        ;
    }
}
