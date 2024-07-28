<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Message;
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
     * Create a new conversation with a user and a message
     *
     * @param string $data
     * @param User $user
     * @return JsonResponse
     */
    public function create(string $data, User $user): JsonResponse
    {
        $decodedData = json_decode($data, true);

        // Check if 'conversationWith' is present in the decoded data
        if (empty($decodedData['conversationWith'])) {
            return new JsonResponse(['message' => 'User is required'], Response::HTTP_BAD_REQUEST);
        }

        // Find the user with whom the conversation is to be created
        $conversationWith = $this->em->getRepository(User::class)->find($decodedData['conversationWith']);

        // Check if the 'conversationWith' user exists
        if (!$conversationWith) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if messages are present in the decoded data
        if (empty($decodedData['message'])) {
            return new JsonResponse(['message' => 'Message is required'], Response::HTTP_BAD_REQUEST);
        }

        // Create a new conversation and assign users
        $conversation = new Conversation();
        $conversation->setName($decodedData['name']);
        $conversation->addUser($user);
        $conversation->addUser($conversationWith);

        // Generate and assign the token and keys
        $generatedToken = $this->encrypt->generateToken();
        $conversation->setToken($generatedToken);

        $keys = $this->encrypt->generateKeys();
        $conversation->setPublicKey($keys['public_key']);
        $conversation->setPrivateKey($this->encrypt->encryptPrivateKey($keys['private_key'], $generatedToken));

        // Persist the conversation entity
        $this->em->persist($conversation);

        // Create and persist the first message
        $message = new Message();
        $message->setContent($decodedData['message']);
        $message->setConversation($conversation);
        $message->setUser($user);

        $this->em->persist($message);

        // Save the changes to the database
        $this->em->flush();

        // JSON response indicating successful operation
        return new JsonResponse(['message' => 'Conversation created successfully', 'conversation_id' => $conversation->getId()], Response::HTTP_CREATED);
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
