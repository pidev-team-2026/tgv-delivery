<?php

namespace App\Repository;

use App\Entity\Partenaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Partenaire>
 */
class PartenaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partenaire::class);
    }

    /**
     * Recherche et tri des partenaires
     */
    public function searchAndSort(string $search, string $sort = 'id', string $direction = 'ASC'): array
    {
        $allowedSorts = ['id', 'nom', 'type', 'email', 'telephone', 'zone'];
        if (!\in_array($sort, $allowedSorts, true)) {
            $sort = 'nom';
        }

        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.zone', 'z')
            ->where('p.nom LIKE :search')
            ->orWhere('p.email LIKE :search')
            ->orWhere('p.type LIKE :search')
            ->orWhere('p.telephone LIKE :search')
            ->orWhere('z.nom LIKE :search')
            ->setParameter('search', '%' . $search . '%')
        ;

        if ($sort === 'zone') {
            $qb->orderBy('z.nom', $direction);
        } else {
            $qb->orderBy('p.' . $sort, $direction);
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Partenaire[] Returns an array of Partenaire objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Partenaire
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}