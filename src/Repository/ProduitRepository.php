<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }


public function findBySearchAndSort(string $search = '', string $sortBy = 'id', string $order = 'ASC'): array
{
    $qb = $this->createQueryBuilder('p');
    
    if (!empty($search)) {
        $qb->where('p.nom LIKE :search')
           ->orWhere('p.description LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }
    
    $allowedSortFields = ['id', 'nom', 'prix', 'stock', 'statut', 'categorie', 'promotion', 'dateCreation', 'dateMisAjour'];
    if (in_array($sortBy, $allowedSortFields)) {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy('p.' . $sortBy, $order);
    }
    
    return $qb->getQuery()->getResult();
}

    //    /**
    //     * @return Produit[] Returns an array of Produit objects
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

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
