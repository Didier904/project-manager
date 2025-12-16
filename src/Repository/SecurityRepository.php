<?php

namespace App\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class SecurityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Security::class);
    }
}
