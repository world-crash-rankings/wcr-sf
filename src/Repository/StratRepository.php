<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Strat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Strat>
 */
class StratRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Strat::class);
    }

    /**
     * Find all strategies for a zone with their cars and top scores
     *
     * @return list<Strat>
     */
    public function findByZoneWithScores(\App\Entity\Zone $zone): array
    {
        // First, get all strats for the zone with cars
        $strats = $this->createQueryBuilder('s')
            ->leftJoin('s.cars', 'c')
            ->addSelect('c')
            ->where('s.zone = :zone')
            ->setParameter('zone', $zone)
            ->orderBy('s.bestTotal', 'DESC')
            ->getQuery()
            ->getResult();

        // For each strat, load top 10 scores
        if (count($strats) > 0) {
            $stratIds = array_map(fn($s) => $s->getId(), $strats);

            // Load scores for all strats at once
            $scores = $this->getEntityManager()
                ->createQuery(
                    'SELECT sc, p, car
                    FROM App\Entity\Score sc
                    LEFT JOIN sc.player p
                    LEFT JOIN sc.car car
                    WHERE sc.strat IN (:stratIds)
                    ORDER BY sc.score DESC'
                )
                ->setParameter('stratIds', $stratIds)
                ->getResult();

            // Group scores by strat and limit to top 10 per strat
            $scoresByStrat = [];
            foreach ($scores as $score) {
                $stratId = $score->getStrat()->getId();
                if (!isset($scoresByStrat[$stratId])) {
                    $scoresByStrat[$stratId] = [];
                }
                if (count($scoresByStrat[$stratId]) < 10) {
                    $scoresByStrat[$stratId][] = $score;
                }
            }
        }

        return $strats;
    }
}
