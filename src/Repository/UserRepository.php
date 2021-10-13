<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    // Récupère les gérants en fonction d'une liste de rôles + renvoi le total si demandé
    public function UsersAllForRole($roles, $user_id, $count=null) {
        $b = $this->createQueryBuilder('u');

        foreach($roles as $key => $val) {
            if($key == 0) {
                $b->where('u.roles LIKE :role'.$key)
                ->setParameter('role'.$key, '%"' . $val . '"%');
            } else {
                $b->orWhere('u.roles LIKE :role'.$key)
                ->setParameter('role'.$key, '%"' . $val . '"%');
            }
        }
        $b->andWhere('u.id != :id')->setParameter('id', $user_id);
        if($count !== null) {
            return $b->addSelect('COUNT(u.id) as total')->getQuery()->getSingleResult();
        } else {
            return $b->getQuery()->getResult();
        }
    }

    // Récupère tous les utilisateurs
    public function findUsers() {
        $b = $this->createQueryBuilder('u')
                ->where("u.roles LIKE '%ROLE_USER%'");
        return $b->getQuery()->getResult();
    }

    // Récupère tous les administrateurs
    public function findAdministrators() {
        $b = $this->createQueryBuilder('u')
                ->where("u.roles LIKE '%ROLE_ADMIN%'");
        return $b->getQuery()->getResult();
    }

    // Compte le nombre total d'utilisateurs
    public function countTotalUsers() {
        $b = $this->createQueryBuilder('u')
                ->addSelect('COUNT(u.id) as total')
                ->where("u.roles LIKE '%ROLE_USER%'");
        return $b->getQuery()->getSingleResult();
    }
}
