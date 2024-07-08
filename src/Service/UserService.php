<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{

    public function __construct(private EntityManagerInterface $em, private UserPasswordHasherInterface $passwordHasher, private SerializerInterface $serializer, private ValidatorService $validatorService)
    {
        
    }
    // Regex for password validation
    const REGEX = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^\w\d\s])\S{8,}$/";

    public function create($data)
    {
        // Deserialize the JSON data to a User object
        $user = $this->serializer->deserialize($data, User::class, 'json');
        // Check if the user object is not empty
        if (!empty($user)) {
            
            if (empty($user->getUsername())) {

                return new JsonResponse(['message' => 'Username is required'], Response::HTTP_BAD_REQUEST);
            }

            if (empty($user->getEmail())) {

                return new JsonResponse(['message' => 'Email is required'], Response::HTTP_BAD_REQUEST);
            }

            if (empty($user->getPassword())) {

                return new JsonResponse(['message' => 'password is required'], Response::HTTP_BAD_REQUEST);
            }

            if (!preg_match(self::REGEX, $user->getPassword())) {

                return new JsonResponse(['message' => 'The password must contain at least 8 characters, one uppercase letter, one lowercase letter, one digit, and one special character'], Response::HTTP_BAD_REQUEST);
            }
            // Validate the user object
            $errors = $this->validatorService->validate($user);
            
            if ($errors) {
            
                return $errors;
            }

            try
            {
                $user->setCreatedAt(new \DateTimeImmutable());
                $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));

                $this->em->persist($user);
                $this->em->flush();

                $newUser = $user->getUsername();
            }
            catch (\Exception $e)
            {
                return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        
        return new JsonResponse(['message' => "User : $newUser created !"], Response::HTTP_CREATED);
    }

}