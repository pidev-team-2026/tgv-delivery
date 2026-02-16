<?php

namespace App\Repository;

use App\Entity\Contrat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contrat>
 */
class ContratRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contrat::class);
    }

    /**
     * Contrats expirés (dateFin < aujourd'hui)
     */
    public function findExpires(): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.partenaire', 'p')
            ->addSelect('p')
            ->where('c.dateFin < :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('c.dateFin', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Contrats expirés pour lesquels la notification n'a pas encore été envoyée
     */
    public function findExpiresSansNotification(): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.partenaire', 'p')
            ->addSelect('p')
            ->where('c.dateFin < :now')
            ->andWhere('c.notificationEnvoyeeAt IS NULL')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    /**
     * Contrats actifs (dateFin >= aujourd'hui)
     */
    public function findActifs(): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.partenaire', 'p')
            ->addSelect('p')
            ->where('c.dateFin >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('c.dateFin', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Contrats expirant dans les X prochains jours
     */
    public function findExpirantBientot(int $jours = 30): array
    {
        $now = new \DateTime();
        $limite = (clone $now)->modify("+{$jours} days");

        return $this->createQueryBuilder('c')
            ->innerJoin('c.partenaire', 'p')
            ->addSelect('p')
            ->where('c.dateFin >= :now')
            ->andWhere('c.dateFin <= :limite')
            ->setParameter('now', $now)
            ->setParameter('limite', $limite)
            ->orderBy('c.dateFin', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des contrats
     */
    public function getStatistiques(): array
    {
        $now = new \DateTime();
        $limite = (clone $now)->modify('+30 days');

        $total = (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $actifs = (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateFin >= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();

        $expires = (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateFin < :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();

        $expirantBientot = (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateFin >= :now')
            ->andWhere('c.dateFin <= :limite')
            ->setParameter('now', $now)
            ->setParameter('limite', $limite)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $total,
            'actifs' => $actifs,
            'expires' => $expires,
            'expirant_bientot' => $expirantBientot,
        ];
    }
}
