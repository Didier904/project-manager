<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findChatBetweenUsers($user1, $user2)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('(m.sender = :u1 AND m.recipient = :u2) OR (m.sender = :u2 AND m.recipient = :u1)')
            ->setParameter('u1', $user1)
            ->setParameter('u2', $user2)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
