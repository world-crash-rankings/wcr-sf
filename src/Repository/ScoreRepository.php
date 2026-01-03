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
            ->leftJoin('s.zone', 'z')
            ->addSelect('z')
            ->leftJoin('s.car', 'c')
            ->addSelect('c')
            ->where('s.player = :player')
            ->andWhere('s.prEntry = true')
            ->andWhere('s.chartRank IS NOT NULL')
            ->setParameter('player', $player)
            ->orderBy('z.id', 'ASC')
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

    /**
     * Get current world records for all zones
     *
     * @return array<int, Score>
     */
    public function getCurrentWorldRecords(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.player', 'p')
            ->addSelect('p')
            ->leftJoin('s.zone', 'z')
            ->addSelect('z')
            ->where('s.chartRank = 1')
            ->orderBy('s.zone', 'ASC');

        $scores = $qb->getQuery()->getResult();

        // Index by zone ID for easy access
        $wrs = [];
        foreach ($scores as $score) {
            $wrs[$score->getZone()->getId()] = $score;
        }

        return $wrs;
    }

    /**
     * Get top scores for a zone
     *
     * @return list<Score>
     */
    public function getTopScores(Zone $zone, int $limit = 25): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.player', 'p')
            ->addSelect('p')
            ->leftJoin('p.country', 'country')
            ->addSelect('country')
            ->leftJoin('s.car', 'c')
            ->addSelect('c')
            ->where('s.zone = :zone')
            ->andWhere('s.chartRank IS NOT NULL')
            ->setParameter('zone', $zone)
            ->orderBy('s.chartRank', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get scores filtered by glitch type
     *
     * @return list<Score>
     */
    public function getUnSortedScores(Zone $zone, string $glitchType, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.player', 'p')
            ->addSelect('p')
            ->leftJoin('p.country', 'country')
            ->addSelect('country')
            ->leftJoin('s.car', 'c')
            ->addSelect('c')
            ->where('s.zone = :zone')
            ->setParameter('zone', $zone);

        if ($glitchType === 'None') {
            $qb->andWhere('s.glitch = :glitch')
                ->setParameter('glitch', \App\Enum\GlitchType::NONE);
        } elseif ($glitchType === 'Sink') {
            $qb->andWhere('s.glitch = :glitch')
                ->setParameter('glitch', \App\Enum\GlitchType::SINK);
        }

        $qb->orderBy('s.score', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Get freeze scores for a zone
     *
     * @return list<Score>
     */
    public function getFreezeScores(Zone $zone): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.player', 'p')
            ->addSelect('p')
            ->leftJoin('p.country', 'country')
            ->addSelect('country')
            ->leftJoin('s.car', 'c')
            ->addSelect('c')
            ->where('s.zone = :zone')
            ->andWhere('s.glitch = :glitch')
            ->setParameter('zone', $zone)
            ->setParameter('glitch', \App\Enum\GlitchType::FREEZE)
            ->orderBy('s.score', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get former world records for a zone
     *
     * @return list<Score>
     */
    public function getFormerWorldRecords(Zone $zone): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.player', 'p')
            ->addSelect('p')
            ->leftJoin('p.country', 'country')
            ->addSelect('country')
            ->leftJoin('s.car', 'c')
            ->addSelect('c')
            ->where('s.zone = :zone')
            ->andWhere('s.formerWr = true')
            ->setParameter('zone', $zone)
            ->orderBy('s.registration', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get best scores with video proof
     *
     * @return list<Score>
     */
    public function getBestScoreVideos(Zone $zone, int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.player', 'p')
            ->addSelect('p')
            ->leftJoin('p.country', 'country')
            ->addSelect('country')
            ->leftJoin('s.car', 'c')
            ->addSelect('c')
            ->where('s.zone = :zone')
            ->andWhere('s.proofType IN (:proofTypes)')
            ->andWhere('s.proofLink IS NOT NULL')
            ->setParameter('zone', $zone)
            ->setParameter('proofTypes', [\App\Enum\ProofType::REPLAY, \App\Enum\ProofType::LIVE])
            ->orderBy('s.score', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get best damage scores with video proof
     *
     * @return list<Score>
     */
    public function getBestDamageVideos(Zone $zone, int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.player', 'p')
            ->addSelect('p')
            ->leftJoin('p.country', 'country')
            ->addSelect('country')
            ->leftJoin('s.car', 'c')
            ->addSelect('c')
            ->where('s.zone = :zone')
            ->andWhere('s.proofType IN (:proofTypes)')
            ->andWhere('s.proofLink IS NOT NULL')
            ->andWhere('s.damage IS NOT NULL')
            ->setParameter('zone', $zone)
            ->setParameter('proofTypes', [\App\Enum\ProofType::REPLAY, \App\Enum\ProofType::LIVE])
            ->orderBy('s.damage', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get best videos by type (Live, Non Glitch)
     *
     * @return array<string, Score|null>
     */
    public function getBestVideosByType(Zone $zone): array
    {
        $typeVids = [
            'Live' => null,
            'Non Glitch' => null,
        ];

        // Best Live proof
        $liveScore = $this->createQueryBuilder('s')
            ->leftJoin('s.player', 'p')
            ->addSelect('p')
            ->leftJoin('p.country', 'country')
            ->addSelect('country')
            ->leftJoin('s.car', 'c')
            ->addSelect('c')
            ->where('s.zone = :zone')
            ->andWhere('s.proofType = :proofType')
            ->andWhere('s.proofLink IS NOT NULL')
            ->setParameter('zone', $zone)
            ->setParameter('proofType', \App\Enum\ProofType::LIVE)
            ->orderBy('s.score', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($liveScore !== null) {
            $typeVids['Live'] = $liveScore;
        }

        // Best Non Glitch proof (if zone has glitch modes)
        if ($zone->isGlitch()) {
            $nonGlitchScore = $this->createQueryBuilder('s')
                ->leftJoin('s.player', 'p')
                ->addSelect('p')
                ->leftJoin('p.country', 'country')
                ->addSelect('country')
                ->leftJoin('s.car', 'c')
                ->addSelect('c')
                ->where('s.zone = :zone')
                ->andWhere('s.glitch = :glitch')
                ->andWhere('s.proofLink IS NOT NULL')
                ->setParameter('zone', $zone)
                ->setParameter('glitch', \App\Enum\GlitchType::NONE)
                ->orderBy('s.score', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($nonGlitchScore !== null) {
                $typeVids['Non Glitch'] = $nonGlitchScore;
            }
        }

        return $typeVids;
    }

    /**
     * Get national records for a country (best score per zone)
     *
     * @return list<Score>
     */
    public function getNationalRecords(int $countryId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT s.*
            FROM scores s
            JOIN players p ON s.player_id = p.id
            WHERE s.id = (
                SELECT lookup.id
                FROM scores lookup
                JOIN players players_tmp ON players_tmp.id = lookup.player_id
                WHERE lookup.zone_id = s.zone_id
                  AND players_tmp.country_id = :countryId
                  AND lookup.chart_rank IS NOT NULL
                ORDER BY lookup.score DESC
                LIMIT 1
            )
            ORDER BY s.zone_id ASC
        ';

        $result = $conn->executeQuery($sql, ['countryId' => $countryId]);
        $scoreIds = array_column($result->fetchAllAssociative(), 'id');

        if (empty($scoreIds)) {
            return [];
        }

        return $this->createQueryBuilder('s')
            ->leftJoin('s.player', 'p')
            ->addSelect('p')
            ->leftJoin('p.country', 'country')
            ->addSelect('country')
            ->leftJoin('s.car', 'c')
            ->addSelect('c')
            ->leftJoin('s.zone', 'z')
            ->addSelect('z')
            ->where('s.id IN (:ids)')
            ->setParameter('ids', $scoreIds)
            ->orderBy('z.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get query for last added scores by player (for pagination)
     *
     * @return \Doctrine\ORM\Query<int, Score>
     */
    public function getLastAddedQuery(Player $player): \Doctrine\ORM\Query
    {
        /** @var \Doctrine\ORM\Query<int, Score> $query */
        $query = $this->createQueryBuilder('s')
            ->leftJoin('s.zone', 'z')
            ->addSelect('z')
            ->leftJoin('s.car', 'c')
            ->addSelect('c')
            ->leftJoin('s.strat', 'st')
            ->addSelect('st')
            ->where('s.player = :player')
            ->setParameter('player', $player)
            ->orderBy('s.registration', 'DESC')
            ->getQuery();

        return $query;
    }

    /**
     * Get query for last achieved scores by player (for pagination)
     *
     * @return \Doctrine\ORM\Query<int, Score>
     */
    public function getLastAchievedQuery(Player $player): \Doctrine\ORM\Query
    {
        /** @var \Doctrine\ORM\Query<int, Score> $query */
        $query = $this->createQueryBuilder('s')
            ->leftJoin('s.zone', 'z')
            ->addSelect('z')
            ->leftJoin('s.car', 'c')
            ->addSelect('c')
            ->leftJoin('s.strat', 'st')
            ->addSelect('st')
            ->where('s.player = :player')
            ->setParameter('player', $player)
            ->orderBy('s.realisation', 'DESC')
            ->getQuery();

        return $query;
    }

    /**
     * Get query for all last added scores (for pagination)
     *
     * @return \Doctrine\ORM\Query<int, Score>
     */
    public function getLastAddedScoresQuery(): \Doctrine\ORM\Query
    {
        /** @var \Doctrine\ORM\Query<int, Score> $query */
        $query = $this->createQueryBuilder('s')
            ->leftJoin('s.player', 'p')
            ->addSelect('p')
            ->leftJoin('p.country', 'c')
            ->addSelect('c')
            ->leftJoin('s.zone', 'z')
            ->addSelect('z')
            ->leftJoin('s.car', 'car')
            ->addSelect('car')
            ->leftJoin('s.strat', 'st')
            ->addSelect('st')
            ->orderBy('s.registration', 'DESC')
            ->getQuery();

        return $query;
    }

    /**
     * Get query for all last achieved scores (for pagination)
     *
     * @return \Doctrine\ORM\Query<int, Score>
     */
    public function getLastAchievedScoresQuery(): \Doctrine\ORM\Query
    {
        /** @var \Doctrine\ORM\Query<int, Score> $query */
        $query = $this->createQueryBuilder('s')
            ->leftJoin('s.player', 'p')
            ->addSelect('p')
            ->leftJoin('p.country', 'c')
            ->addSelect('c')
            ->leftJoin('s.zone', 'z')
            ->addSelect('z')
            ->leftJoin('s.car', 'car')
            ->addSelect('car')
            ->leftJoin('s.strat', 'st')
            ->addSelect('st')
            ->orderBy('s.realisation', 'DESC')
            ->getQuery();

        return $query;
    }
}
