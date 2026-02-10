<?php

namespace App\Repository;

use App\Entity\RendezVous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RendezVous>
 */
class RendezVousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RendezVous::class);
    }

    /**
     * Back Office : recherche (message, état, nom commerçant) et tri.
     *
     * @return RendezVous[]
     */
    public function findWithSearchAndSort(string $search, string $sort = 'dateRdv', string $order = 'desc'): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.commercant', 'c')
            ->addSelect('c');
        if ($search !== '') {
            $qb->andWhere('r.message LIKE :q OR r.etat LIKE :q OR c.nom LIKE :q')
                ->setParameter('q', '%' . $search . '%');
        }
        $allowedSort = ['id', 'dateRdv', 'etat', 'message', 'commercant'];
        if (!\in_array($sort, $allowedSort, true)) {
            $sort = 'dateRdv';
        }
        if ($sort === 'commercant') {
            $qb->orderBy('c.nom', $order === 'desc' ? 'DESC' : 'ASC');
        } else {
            $qb->orderBy('r.' . $sort, $order === 'desc' ? 'DESC' : 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return RendezVous[] Returns an array of RendezVous objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?RendezVous
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
