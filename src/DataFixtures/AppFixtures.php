<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Factory\ClientFactory;
use App\Factory\ServiceWimaxFactory;
use App\Factory\ServiceFtthFactory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        ClientFactory::createMany(10, function () {
            return [
                // Definimos los servicios dentro del array de atributos
                'services' => [
                    // Creamos una mezcla de Wimax y FTTH
                    ...ServiceWimaxFactory::createMany(rand(1, 2)),
                    ...ServiceFtthFactory::createMany(rand(0, 1)),
                ],
            ];
        });
    }
}
