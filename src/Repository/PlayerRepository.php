<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Player>
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function findByNameUrl(string $nameUrl): ?Player
    {
        return $this->findOneBy(['nameUrl' => $nameUrl]);
    }

    /**
     * @return Player[]
     */
    public function findByTotalRank(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.country', 'c')
            ->addSelect('c')
            ->where('p.totalRank <= :limit')
            ->setParameter('limit', $limit)
            ->orderBy('p.total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Player[]
     */
    public function findByAvgPosRank(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.country', 'c')
            ->addSelect('c')
            ->where('p.avgPosRank <= :limit')
            ->setParameter('limit', $limit)
            ->orderBy('p.avgPos', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Player[]
     */
    public function findByAvgStarsRank(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.country', 'c')
            ->addSelect('c')
            ->where('p.avgStarsRank <= :limit')
            ->setParameter('limit', $limit)
            ->orderBy('p.avgStars', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Player[]
     */
    public function findByAvgPercentRank(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.country', 'c')
            ->addSelect('c')
            ->where('p.avgPercentRank <= :limit')
            ->setParameter('limit', $limit)
            ->orderBy('p.avgPercent', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
