<?php

namespace App\Controller;

use App\Service\UserService;
use App\Repository\ConversationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/user/', name: 'app_user_')]
class UserController extends AbstractController
{
    /**
     * Create a new user
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    #[Route('create', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserService $userService): JsonResponse
    {
        // Call the create method from the UserService
        return $userService->create($request->getContent());
    }

    /**
     * Get the current user info
     *
     * @return JsonResponse
     */
    #[Route('get_info', name: 'get_info', methods: ['GET'])]
    public function get_info(): JsonResponse
    {

        return $this->json($this->getUser(), Response::HTTP_OK, [], ["groups" => "users"]);
    }

    /**
     * Get all conversations for the current user
     *
     * @param ConversationRepository $conversationRepo
     * @return JsonResponse
     */
    #[Route('get_conversations', name: 'get_conversations', methods: ['GET'])]
    public function get_conversations(ConversationRepository $conversationRepo): JsonResponse
    {

        $conversation = $conversationRepo->findUserConversations($this->getUser());

        return $this->json($conversation, Response::HTTP_OK, [], ["groups" => "conversation"]);
    }
}
