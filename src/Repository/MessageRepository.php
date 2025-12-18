<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Récupère toutes les conversations d'un utilisateur
     * Dernier message par interlocuteur
     */
    public function findUserConversations(User $user): array
    {
        // Récupère tous les messages où l'utilisateur est sender ou recipient
        $messages = $this->createQueryBuilder('m')
            ->where('m.sender = :user OR m.recipient = :user')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $conversations = [];

        foreach ($messages as $message) {
            $other = $message->getSender() === $user ? $message->getRecipient() : $message->getSender();
            if (!$other) continue; // sécurité si user supprimé

            $key = $other->getId();

            // On garde uniquement le dernier message par interlocuteur
            if (!isset($conversations[$key])) {
                $conversations[$key] = $message;
            }
        }

        // Tri par date du dernier message
        usort($conversations, function ($a, $b) {
            return $b->getCreatedAt() <=> $a->getCreatedAt();
        });

        return array_values($conversations);
    }

    /**
     * Messages entre deux utilisateurs
     */
    public function findChatBetweenUsers(User $user, User $other): array
    {
        return $this->createQueryBuilder('m')
            ->where('(m.sender = :u AND m.recipient = :o) OR (m.sender = :o AND m.recipient = :u)')
            ->setParameter('u', $user)
            ->setParameter('o', $other)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
