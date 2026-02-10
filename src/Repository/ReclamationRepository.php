<?php

namespace App\Repository;

use App\Entity\Reclamation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    /**
     * @return Reclamation[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Reclamation[]
     */
    public function findByUserAndSearch(User $user, ?string $search): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.createdAt', 'DESC');

        if ($search !== null && $search !== '') {
            $qb->andWhere('r.subject LIKE :search OR r.message LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Reclamation[]
     */
    public function findAllWithSearchAndSort(?string $search, ?string $sortBy = 'createdAt', ?string $sortOrder = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u');

        if ($search) {
            $qb->andWhere('r.subject LIKE :search OR r.message LIKE :search OR u.name LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $allowedSort = ['createdAt', 'updatedAt', 'subject', 'status'];
        if (in_array($sortBy, $allowedSort)) {
            $qb->orderBy('r.' . $sortBy, $sortOrder === 'ASC' ? 'ASC' : 'DESC');
        } else {
            $qb->orderBy('r.createdAt', 'DESC');
        }

        return $qb->getQuery()->getResult();
    }

    public function save(Reclamation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Reclamation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Reclamation[]
     */
    public function findCreatedBetween(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.createdAt >= :from')
            ->andWhere('r.createdAt <= :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('r.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
