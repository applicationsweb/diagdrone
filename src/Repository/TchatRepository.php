<?php

namespace App\Repository;

use App\Entity\Tchat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tchat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tchat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tchat[]    findAll()
 * @method Tchat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TchatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tchat::class);
    }

    // Récupère une conversation entre un utilisateur et un administrateur
    public function findCommunicationByReceiverAndSender($receiver_id, $sender_id)
    {
        return $this->createQueryBuilder('c')
            ->join('c.sender', 's')
            ->join('c.receiver', 'r')
            ->andWhere('s.id = :sender_id AND r.id = :receiver_id')
            ->orWhere('s.id = :receiver_id AND r.id = :sender_id')
            ->setParameter('receiver_id', $receiver_id)
            ->setParameter('sender_id', $sender_id)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    // Récupère le nombre de messages non lus pour un utilisateur
    public function findUnread($sender_id, $receiver_id)
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.isRead) as unread')
            ->join('c.sender', 's')
            ->join('c.receiver', 'r')
            ->andWhere('s.id = :sender_id AND r.id = :receiver_id')
            ->setParameter('receiver_id', $receiver_id)
            ->setParameter('sender_id', $sender_id)
            ->andWhere('c.isRead = 0')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getSingleResult()
        ;
    }

    // Récupère les messages non lus
    public function findUnReadMessage($receiver_id, $sender_id)
    {
        return $this->createQueryBuilder('c')
            ->join('c.receiver', 'r')
            ->join('c.sender', 's')
            ->andWhere('r.id = :receiver_id AND c.isRead = 0')
            ->andWhere('s.id = :sender_id')
            ->setParameter('receiver_id', $receiver_id)
            ->setParameter('sender_id', $sender_id)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
