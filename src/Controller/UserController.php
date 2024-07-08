<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/user_', name: 'app_user_')]
class UserController extends AbstractController
{
    #[Route('create', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserService $userService): JsonResponse
    {
        // Call the create method from the UserService
        return $userService->create($request->getContent());
    }
}