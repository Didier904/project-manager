<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Met à jour automatiquement le mot de passe hashé de l'utilisateur.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances de "%s" ne sont pas supportées.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    // Tu peux ajouter ici des méthodes personnalisées pour rechercher des utilisateurs
    // Exemple :
    // public function findByEmail(string $email): ?User
    // {
    //     return $this->createQueryBuilder('u')
    //         ->andWhere('u.email = :email')
    //         ->setParameter('email', $email)
    //         ->getQuery()
    //         ->getOneOrNullResult();
    // }
}
