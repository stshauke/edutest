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
     * 🔐 Rehash automatique du mot de passe si nécessaire
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * 🔍 Trouve les utilisateurs ayant un rôle spécifique
     * (exemple : findByRole('ROLE_TEACHER'))
     */
    public function findByRole(string $role): array
    {
        // On récupère tous les utilisateurs
        $users = $this->findAll();

        // On filtre en PHP (Doctrine ne supporte pas JSON_CONTAINS)
        return array_filter($users, fn(User $u) => in_array($role, $u->getRoles(), true));
    }

    /**
     * 🧮 Compte le nombre total d’utilisateurs approuvés pour un rôle donné
     */
    public function countApprovedByRole(string $role): int
    {
        $users = $this->findByRole($role);
        return count(array_filter($users, fn(User $u) => $u->isApproved()));
    }

    /**
     * 🧮 Compte tous les utilisateurs d’un rôle (approuvés ou non)
     */
    public function countByRole(string $role): int
    {
        return count($this->findByRole($role));
    }
}
