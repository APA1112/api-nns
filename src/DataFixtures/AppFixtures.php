<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Factory\ClientFactory;
use App\Factory\RoleFactory;
use App\Factory\ServiceWimaxFactory;
use App\Factory\ServiceFtthFactory;
use App\Factory\UserFactory;
use App\Factory\TicketFactory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. Creamos clientes con servicios asociados o no y sus servicios
        ClientFactory::createMany(10, function () {
            return [
                'services' => [
                    ...ServiceWimaxFactory::createMany(rand(1, 2)),
                    ...ServiceFtthFactory::createMany(rand(0, 1)),
                ],
            ];
        });
        // 2. ROLES: En lugar de createMany(6), definimos explícitamente cuáles queremos asegurar
        // findOrCreate busca por los atributos pasados. Si ya existe, no hace nada.
        $roles = ['ROLE_SAT', 'ROLE_SAC', 'ROLE_GYT', 'ROLE_INSTALADOR', 'ROLE_COORDINADOR', 'ROLE_ADMIN'];

        foreach ($roles as $roleName) {
            RoleFactory::findOrCreate(['name' => $roleName]);
        }

        // 3. USUARIOS:
        // Asegúrate de que en UserFactory::defaults() uses 'userRole' => RoleFactory::random()
        // Si ya tienes usuarios con ciertos emails, Faker podría fallar. 
        // Puedes forzar emails únicos o dejar que cree 10 más con emails aleatorios.
        UserFactory::createMany(10);
        // 4. CREAR TICKETS
        // Al llamar a este Factory, automáticamente:
        // - Elegirá un User, Role y Service aleatorio de los creados arriba.
        // - Creará un TicketComment inicial (definido en el afterInstantiate del factory).
        TicketFactory::createMany(20);

        $manager->flush();
    }
}
