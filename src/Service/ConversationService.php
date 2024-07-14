<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Conversation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class ConversationService
{

    public function __construct(private EncryptionService $encrypt, private EntityManagerInterface $em, private SerializerInterface $serializer)
    {
    }

    /**
     * Create a new conversation
     *
     * @param string $data
     * @param User $user
     * @return JsonResponse
     */
    public function create(string $data, User $user): JsonResponse
    {
        $decodedData = json_decode($data, true);

        if (empty($decodedData['conversationWith'])) {
            return new JsonResponse(['message' => 'User is required'], Response::HTTP_BAD_REQUEST);
        }

        $conversationWith = $this->em->getRepository(User::class)->find($decodedData['conversationWith']);

        if (!$conversationWith) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $conversation = $this->serializer->deserialize($data, Conversation::class, 'json');

        if (empty($conversation->getName())) {
            return new JsonResponse(['message' => 'Name is required'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($conversation->getName())) {
            return new JsonResponse(['message' => 'Name is required'], Response::HTTP_BAD_REQUEST);
        }

        $generatedToken = $this->encrypt->generateToken();
        $conversation->setToken($generatedToken);

        $keys = $this->encrypt->generateKeys();

        $conversation->setPublicKey($keys['public_key']);
        $conversation->setPrivateKey($this->encrypt->encryptPrivateKey($keys['private_key'], $generatedToken));

        $conversation->addUser($user);
        $conversation->addUser($conversationWith);

        $this->em->persist($conversation);
        $this->em->flush();

        return new JsonResponse(['message' => 'Conversation created successfully'], Response::HTTP_CREATED);
    }

    /**
     * Add a user to a conversation
     *
     * @param string $data
     * @param Conversation $conversation
     * @return JsonResponse
     */
    public function addUser(string $data, Conversation $conversation, User $user): JsonResponse
    {

        $user = $this->em->getRepository(User::class)->find($user);

        if (!in_array($user, $conversation->getUsers()->toArray())) {
            return new JsonResponse(['message' => 'You are not part of this conversation'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($data, true);

        if (empty($data['user_id'])) {
            return new JsonResponse(['message' => 'User ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->em->getRepository(User::class)->find($data['user_id']);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $conversation->addUser($user);

        $this->em->persist($conversation);
        $this->em->flush();

        return new JsonResponse(['message' => 'User added to conversation successfully'], Response::HTTP_OK);
    }
}
