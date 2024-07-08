<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/user_', name: 'app_user_')]
class UserController extends AbstractController
{
    #[Route('create', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data) {
            if (empty($data['username'])) {

                return $this->json(['message' => 'Username is required'], Response::HTTP_BAD_REQUEST);
            }

            if (empty($data['email'])) {

                return $this->json(['message' => 'Email is required'], Response::HTTP_BAD_REQUEST);
            }

            if (empty($data['password'])) {

                return $this->json(['message' => 'password is required'], Response::HTTP_BAD_REQUEST);
            }

            try
            {
                $user = new User();
                $user->setUsername($data['username']);
                $user->setEmail($data['email']);

                $user->setPassword($passwordHasher->hashPassword($user, $data['password']));

                $em->persist($user);
                $em->flush();

            }
            catch (\Exception $e)
            {
                return $this->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return $this->json(['message' => "User created !"], Response::HTTP_CREATED);
    }
}