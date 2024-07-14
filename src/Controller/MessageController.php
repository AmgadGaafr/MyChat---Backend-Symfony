<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Service\MessageService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/message/', name: 'app_message_')]
class MessageController extends AbstractController
{
    /**
     * Create a new message
     * 
     * @param Request $request
     * @param Conversation $conversation
     * @param MessageService $message
     * @return JsonResponse
     */
    #[Route('create/{id}', name: 'create')]
    public function create(Request $request, Conversation $conversation, MessageService $message): JsonResponse
    {
        // Call the create method from the MessageService class
        return $message->create($conversation, $request->getContent(), $this->getUser());
    }

    /**
     * Get all messages from a conversation
     * 
     * @param Conversation $conversation
     * @param MessageService $message
     * @return JsonResponse
     */
    #[Route('get/{id}', name: 'get', methods: ['GET'])]
    public function get_messages(Conversation $conversation, MessageService $message): JsonResponse
    {
        // Call the getMessage method from the MessageService class
        return $message->getMessages($conversation, $this->getUser());
    }
}
