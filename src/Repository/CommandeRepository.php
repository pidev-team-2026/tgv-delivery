<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }
   
   
public function findBySearchAndSort(string $search = '', string $sortBy = 'id', string $order = 'DESC'): array
{
    $qb = $this->createQueryBuilder('c');
    
 
    if (!empty($search)) {
        $qb->where('c.reference LIKE :search')
           ->orWhere('c.statut LIKE :search')
           ->orWhere('c.nomClient LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }
    
    $allowedSortFields = ['id', 'reference', 'totalPrix', 'statut', 'nomClient', 'ville', 'modePaiement', 'paiementEffectue', 'fraisLivraison', 'dateCreation', 'dateMisAjour', 'dateLivraisonEffective'];
    if (in_array($sortBy, $allowedSortFields)) {
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy('c.' . $sortBy, $order);
    } else {
        $qb->orderBy('c.id', 'DESC');
    }
    
    return $qb->getQuery()->getResult();
}
//    /**
//     * @return Commande[] Returns an array of Commande objects
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

//    public function findOneBySomeField($value): ?Commande
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
