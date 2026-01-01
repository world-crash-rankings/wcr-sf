<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Player;
use App\Entity\Score;
use App\Entity\Zone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Score>
 */
class ScoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Score::class);
    }

    /**
     * @return list<Score>
     */
    public function findPersonalRecords(Player $player): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.player = :player')
            ->andWhere('s.prEntry = true')
            ->setParameter('player', $player)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<Score>
     */
    public function findByZoneRanked(Zone $zone): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.zone = :zone')
            ->andWhere('s.chartRank IS NOT NULL')
            ->orderBy('s.chartRank', 'ASC')
            ->setParameter('zone', $zone)
            ->getQuery()
            ->getResult();
    }
}
