<?php

namespace App\Repository;

use App\Entity\Commercant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commercant>
 */
class CommercantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commercant::class);
    }

    /**
     * Pour le Front Office : recherche et tri.
     *
     * @return Commercant[]
     */
    public function findForFront(string $search, string $sort = 'nom', string $order = 'asc'): array
    {
        $qb = $this->createQueryBuilder('c');
        if ($search !== '') {
            $qb->andWhere('c.nom LIKE :q OR c.email LIKE :q OR c.metier LIKE :q OR c.ville LIKE :q')
                ->setParameter('q', '%' . $search . '%');
        }
        $allowedSort = ['nom', 'email', 'id', 'metier', 'ville'];
        if (!\in_array($sort, $allowedSort, true)) {
            $sort = 'nom';
        }
        $qb->orderBy('c.' . $sort, $order === 'desc' ? 'DESC' : 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Back Office : recherche et tri (nom, email, téléphone).
     *
     * @return Commercant[]
     */
    public function findWithSearchAndSort(string $search, string $sort = 'nom', string $order = 'asc'): array
    {
        $qb = $this->createQueryBuilder('c');
        if ($search !== '') {
            $qb->andWhere('c.nom LIKE :q OR c.email LIKE :q OR c.Numero_telephone LIKE :q OR c.metier LIKE :q OR c.ville LIKE :q')
                ->setParameter('q', '%' . $search . '%');
        }
        $allowedSort = ['nom', 'email', 'id', 'metier', 'ville'];
        if (!\in_array($sort, $allowedSort, true)) {
            $sort = 'nom';
        }
        $qb->orderBy('c.' . $sort, $order === 'desc' ? 'DESC' : 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Statistiques : nombre de commerçants par ville.
     *
     * @return array<array{ville: string|null, total: int}>
     */
    public function countByVille(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.ville AS ville, COUNT(c.id) AS total')
            ->groupBy('c.ville')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    //    /**
    //     * @return Commercant[] Returns an array of Commercant objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Commercant
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
