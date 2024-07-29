<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Message;
use App\Entity\Conversation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * Get user conversations with the last message
     *
     * @param User $user
     * @return array
     */
    public function findUserConversations(User $user): array
    {
        $em = $this->getEntityManager();

        // Step 1: Get conversations with at least one message
        $conversations = $em->createQueryBuilder()
            ->select('c')
            ->from(Conversation::class, 'c')
            ->innerJoin('c.messages', 'm') // Ensure there is at least one message
            ->innerJoin('c.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->groupBy('c.id')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Step 2: Get the last message for each conversation
        $results = [];
        foreach ($conversations as $conversation) {
            $latestMessage = $em->createQueryBuilder()
                ->select('m')
                ->from(Message::class, 'm')
                ->where('m.conversation = :conversation')
                ->setParameter('conversation', $conversation->getId())
                ->orderBy('m.createdAt', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            $results[] = [
                'conversation' => $conversation,
                'lastMessage' => $latestMessage,
            ];
        }

        return $results;
    }

    //    public function findOneBySomeField($value): ?Conversation
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
