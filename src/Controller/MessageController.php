<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Conversation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/message_', name: 'app_message_')]
class MessageController extends AbstractController
{
    #[Route('create/{id}', name: 'create')]
    public function create(Request $request, Conversation $conversation, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['content'])) {
            return $this->json([
                'message' => 'Content is required', Response::HTTP_BAD_REQUEST
            ]);
        }

        $message = new Message();
        $message->setContent($data['content']);
        $message->setConversation($conversation);
        $message->setUser($this->getUser());

        $em->persist($message);
        $em->flush();

        return $this->json([
            'message' => 'Message created successfully', Response::HTTP_OK
        ]);
    }

    #[Route('get/{id}', name: 'get', methods: ['GET'])]
    public function get_messages(Conversation $conversation, EntityManagerInterface $em): JsonResponse
    {
        $messages = $em->getRepository(Message::class)->findBy(['conversation' => $conversation]);

        $data = [];

        foreach ($messages as $message) {
            $data[] = [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'user' => $message->getUser()->getUsername(),
                'created_at' => $message->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return $this->json($data, Response::HTTP_OK);
    }
}
