<?php

namespace App\Repository;

use App\Entity\Livreur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LivreurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livreur::class);
    }

    /** Tous les livreurs disponibles */
    public function findDisponibles(): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.statut = :s')
            ->setParameter('s', Livreur::STATUT_DISPONIBLE)
            ->orderBy('l.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** Livreurs internes disponibles */
    public function findPropresDisponibles(): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.statut = :s AND l.type = :t')
            ->setParameter('s', Livreur::STATUT_DISPONIBLE)
            ->setParameter('t', Livreur::TYPE_PROPRE)
            ->orderBy('l.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** Livreurs partenaires disponibles */
    public function findPartenairesDisponibles(): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.statut = :s AND l.type = :t')
            ->setParameter('s', Livreur::STATUT_DISPONIBLE)
            ->setParameter('t', Livreur::TYPE_PARTENAIRE)
            ->orderBy('l.societePartenaire', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** Tous les livreurs avec leurs commandes en cours */
    public function findAllWithStats(): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.commandes', 'c')
            ->addSelect('COUNT(c.id) as nbCommandes')
            ->groupBy('l.id')
            ->orderBy('l.statut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
