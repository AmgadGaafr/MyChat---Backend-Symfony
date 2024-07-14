<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Service\ConversationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/conversation/', name: 'app_conversation_')]
class ConversationController extends AbstractController
{
    /**
     * Create a new conversation
     *
     * @param Request $request
     * @param ConversationService $conversation
     * @return JsonResponse
     */
    #[Route('create', name: 'create', methods: ['POST'])]
    public function create(Request $request, ConversationService $conversation): JsonResponse
    {
        // Call the create method from the ConversationService
        return $conversation->create($request->getContent(), $this->getUser());
    }

    /**
     * Add a user to a conversation
     *
     * @param Request $request
     * @param Conversation $conversation
     * @param ConversationService $conversationService
     * @return JsonResponse
     */
    #[Route('{id}/add_user', name: 'add_user', methods: ['POST'])]
    public function addUser(Request $request, Conversation $conversation, ConversationService $conversationService): JsonResponse
    {
        // Call the addUser method from the ConversationService
        return $conversationService->addUser($request->getContent(), $conversation, $this->getUser());
    }
}
