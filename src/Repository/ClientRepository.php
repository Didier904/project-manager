<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use Doctrine\ORM\QueryBuilder;

class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Recherche globale sur tous les clients
     * @param string|null $q
     * @return Client[]
     */
    public function searchGlobal(?string $q): array
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC');

        if ($q) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('c.nom', ':q'),
                    $qb->expr()->like('c.email', ':q'),
                    $qb->expr()->like('c.societe', ':q')
                )
            )
                ->setParameter('q', '%' . $q . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne tous les clients triés par date de création
     * @return Client[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
