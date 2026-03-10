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
    #[Route('/api/users', name:('api_users_index'), methods: ['GET'])]
    public function index(UserRepository $repository): JsonResponse
    {   
        return $this->json($repository->findAll(), context: ['groups' => 'ticket:read']);
    }

    #[Route('/api/users', methods: ['POST'])]
    // #[IsGranted('ROLE_ADMIN')] // Solo admins pueden crear
    public function create(
        Request $request, 
        EntityManagerInterface $em, 
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email']);
        
        // Hashear el password antes de guardar
        $hashedPassword = $hasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Aquí deberías buscar la entidad Role y asignarla
        // $user->setUserRole($roleEntity);

        $em->persist($user);
        $em->flush();

        return $this->json(['message' => 'Usuario creado'], Response::HTTP_CREATED);
    }

    #[Route('/api/users/{id}', methods: ['PUT'])]
    public function update(
        User $user, 
        Request $request, 
        EntityManagerInterface $em, 
        UserPasswordHasherInterface $hasher
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // O validar si es el mismo usuario
        
        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) $user->setEmail($data['email']);
        if (isset($data['password'])) {
            $user->setPassword($hasher->hashPassword($user, $data['password']));
        }

        $em->flush();
        return $this->json(['message' => 'Usuario actualizado']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    //#[IsGranted('ROLE_ADMIN')]
    public function delete(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
