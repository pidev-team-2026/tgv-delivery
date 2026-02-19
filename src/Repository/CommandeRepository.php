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

    /** Commandes récentes (pour le dashboard admin) */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** Revenus du mois en cours */
    public function getRevenusMois(): float
    {
        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.totalPrix)')
            ->where('c.dateCreation >= :debut')
            ->andWhere('c.statut != :annulee')
            ->setParameter('debut', (new \DateTime())->modify('first day of this month')->setTime(0, 0))
            ->setParameter('annulee', 'annulee')
            ->getQuery()
            ->getSingleScalarResult();
        return (float) ($result ?? 0);
    }

    /** Commandes créées aujourd'hui */
    public function countAujourdhui(): int
    {
        $result = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateCreation >= :minuit')
            ->setParameter('minuit', (new \DateTime())->setTime(0, 0, 0))
            ->getQuery()
            ->getSingleScalarResult();
        return (int) ($result ?? 0);
    }

    /** Revenus du jour */
    public function getRevenusAujourdhui(): float
    {
        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.totalPrix)')
            ->where('c.dateCreation >= :minuit')
            ->andWhere('c.statut != :annulee')
            ->setParameter('minuit', (new \DateTime())->setTime(0, 0, 0))
            ->setParameter('annulee', 'annulee')
            ->getQuery()
            ->getSingleScalarResult();
        return (float) ($result ?? 0);
    }

    /** Nombre de commandes en livraison ou livrées (pour "livraisons actives") */
    public function countEnLivraisonOuLivrees(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.statut IN (:statuts)')
            ->setParameter('statuts', ['en_livraison', 'livree'])
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** Livraisons aujourd'hui (statut livree avec date du jour) ou en cours */
    public function countLivraisonsAujourdhui(): int
    {
        $result = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.statut IN (:statuts)')
            ->andWhere('c.dateCreation >= :minuit')
            ->setParameter('statuts', ['en_livraison', 'livree'])
            ->setParameter('minuit', (new \DateTime())->setTime(0, 0, 0))
            ->getQuery()
            ->getSingleScalarResult();
        return (int) ($result ?? 0);
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
