<?php

namespace App\Repository;

use App\Entity\Emprunt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Emprunt>
 */
class EmpruntRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emprunt::class);
    }

    public function countEmpruntsActifsParUtilisateur(int $utilisateurId): int
    {
        return (int)$this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.utilisateur = :uid')->setParameter('uid', $utilisateurId)
            ->andWhere('e.dateRetour IS NULL')
            ->getQuery()->getSingleScalarResult();
    }

    public function livreEstDejaEmprunte(int $livreId): bool
    {
        return (bool)$this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.livre = :lid')->setParameter('lid', $livreId)
            ->andWhere('e.dateRetour IS NULL')
            ->getQuery()->getSingleScalarResult();
    }

    //    /**
    //     * @return Emprunt[] Returns an array of Emprunt objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Emprunt
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
