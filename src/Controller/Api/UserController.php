<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\Role;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag(name: "Users")]
final class UserController extends AbstractController
{
    #[Route('/api/users', name: 'api_users_index', methods: ['GET'])]
    public function index(UserRepository $repository): JsonResponse
    {   
        // Buscamos todos los usuarios
        $users = $repository->findAll();

        // El contexto 'groups' filtra qué campos salen en el JSON
        return $this->json($users, Response::HTTP_OK, [], [
            'groups' => 'user:read',
            'enable_max_depth' => true
        ]);
    }

    #[Route('/api/users', methods: ['POST'])]
    public function create(
        Request $request, 
        EntityManagerInterface $em, 
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // 1. Validación básica de datos de entrada
        if (!isset($data['email'], $data['password'], $data['role_id'])) {
            return $this->json(['error' => 'Faltan datos obligatorios'], Response::HTTP_BAD_REQUEST);
        }

        // 2. Buscar la entidad Role por su ID
        $roleRepo = $em->getRepository(Role::class);
        $roleEntity = $roleRepo->find($data['role_id']);

        if (!$roleEntity) {
            return $this->json(['error' => 'El rol especificado no existe'], Response::HTTP_NOT_FOUND);
        }

        // 3. Crear el usuario
        $user = new User();
        $user->setEmail($data['email']);
        
        // Hashear password
        $hashedPassword = $hasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // 4. Asignar la relación
        $user->setUserRole($roleEntity);

        $em->persist($user);
        $em->flush();

        return $this->json(['message' => 'Usuario creado con éxito'], Response::HTTP_CREATED);
    }

    #[Route('/api/users/{id}', methods: ['PUT'])]
    public function update(
        User $user, 
        Request $request, 
        EntityManagerInterface $em, 
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) $user->setEmail($data['email']);
        if (isset($data['password'])) {
            $user->setPassword($hasher->hashPassword($user, $data['password']));
        }

        $em->flush();
        return $this->json(['message' => 'Usuario actualizado']);
    }

    #[Route('/api/{id}', methods: ['DELETE'])]
    //#[IsGranted('ROLE_ADMIN')]
    public function delete(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
