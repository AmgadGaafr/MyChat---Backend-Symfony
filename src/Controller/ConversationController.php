<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Service\EncryptionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConversationController extends AbstractController
{
    #[Route('/conversation', name: 'app_conversation', methods: ['POST'])]
    public function createConversation(Request $request, EncryptionService $encrypt): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (empty($data['name'])) {
            return $this->json([
                'message' => 'Name is required',
                'status' => '400'
            ]);
        }

        $conversation = new Conversation();
        $conversation->setName($data['name']);

        $generatedToken = $encrypt->generateToken();
        $conversation->setToken($generatedToken);

        $keys = $encrypt->generateKeys();

        $conversation->setPublicKey($keys['public_key']);
        $conversation->setPrivateKey($encrypt->encryptPrivateKey($keys['private_key'], $generatedToken));
        
        $conversation->addUser($this->getUser());

        return $this->json([
            'message' => 'Conversation created successfully',
            'status' => '200'
        ]);
    }
}

