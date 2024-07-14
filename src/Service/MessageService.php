<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Message;
use App\Entity\Conversation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class MessageService
{

    public function __construct(private EntityManagerInterface $em, private SerializerInterface $serializer)
    {
    }

    /**
     * Create a new message
     *
     * @param Conversation $conversation
     * @param string $data
     * @param User $user
     * @return JsonResponse
     */
    public function create(Conversation $conversation, string $data, User $user): JsonResponse
    {
        $conversation = $this->em->getRepository(Conversation::class)->find($conversation);

        if (!in_array($user, $conversation->getUsers()->toArray())) {
            return new JsonResponse(['message' => 'You are not part of this conversation'], Response::HTTP_FORBIDDEN);
        }

        $message = $this->serializer->deserialize($data, Message::class, 'json');

        if (empty($message->getContent())) {
            return new JsonResponse(['message' => 'Content is required'], Response::HTTP_BAD_REQUEST);
        }

        $message->setConversation($conversation);
        $message->setUser($user);

        $this->em->persist($message);
        $this->em->flush();

        return new JsonResponse(['message' => 'Message created successfully'], Response::HTTP_OK);
    }

    /**
     * Get all messages from a conversation
     *
     * @param Conversation $conversation
     * @param User $user
     * @return JsonResponse
     */
    public function getMessages(Conversation $conversation, User $user): JsonResponse
    {
        $conversation = $this->em->getRepository(Conversation::class)->find($conversation);

        if (!in_array($user, $conversation->getUsers()->toArray())) {
            return new JsonResponse(['message' => 'You are not part of this conversation'], Response::HTTP_FORBIDDEN);
        }

        $messages = $this->em->getRepository(Message::class)->findBy(['conversation' => $conversation]);
        $messages = $this->serializer->serialize($messages, 'json', ['groups' => 'message']);

        return new JsonResponse($messages, Response::HTTP_OK, [], true);
    }
}
