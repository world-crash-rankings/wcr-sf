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
        /** @var list<Score> $result */
        $result = $this->createQueryBuilder('s')
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

        return $result;
    }

    /**
     * @return list<Score>
     */
    public function findByZoneRanked(Zone $zone): array
    {
        /** @var list<Score> $result */
        $result = $this->createQueryBuilder('s')
            ->where('s.zone = :zone')
            ->andWhere('s.chartRank IS NOT NULL')
            ->orderBy('s.chartRank', 'ASC')
            ->setParameter('zone', $zone)
            ->getQuery()
            ->getResult();

        return $result;
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

        /** @var Score[] $scores */
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
        /** @var list<Score> $result */
        $result = $this->createQueryBuilder('s')
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

        return $result;
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

        /** @var list<Score> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Get freeze scores for a zone
     *
     * @return list<Score>
     */
    public function getFreezeScores(Zone $zone): array
    {
        /** @var list<Score> $result */
        $result = $this->createQueryBuilder('s')
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

        return $result;
    }

    /**
     * Get former world records for a zone
     *
     * @return list<Score>
     */
    public function getFormerWorldRecords(Zone $zone): array
    {
        /** @var list<Score> $result */
        $result = $this->createQueryBuilder('s')
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

        return $result;
    }

    /**
     * Get best scores with video proof
     *
     * @return list<Score>
     */
    public function getBestScoreVideos(Zone $zone, int $limit = 10): array
    {
        /** @var list<Score> $result */
        $result = $this->createQueryBuilder('s')
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

        return $result;
    }

    /**
     * Get best damage scores with video proof
     *
     * @return list<Score>
     */
    public function getBestDamageVideos(Zone $zone, int $limit = 10): array
    {
        /** @var list<Score> $result */
        $result = $this->createQueryBuilder('s')
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

        return $result;
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
        /** @var Score|null $liveScore */
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
            /** @var Score|null $nonGlitchScore */
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

        /** @var list<Score> $result */
        $result = $this->createQueryBuilder('s')
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

        return $result;
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

    /**
     * Get best videos for each zone with optional filters
     *
     * @param array<string, string> $params
     * @return list<Score>
     */
    public function getBestVideos(array $params): array
    {
        $conn = $this->getEntityManager()->getConnection();

        // Build WHERE conditions for subquery
        $conditions = ['lookup.proof_link IS NOT NULL', "lookup.proof_type IN ('Replay', 'Live')", "lookup.glitch != 'Sink'"];
        $parameters = [];

        if (isset($params['platform'])) {
            $conditions[] = 'lookup.platform = :platform';
            $parameters['platform'] = $params['platform'];
        }

        if (isset($params['freq'])) {
            $conditions[] = 'lookup.freq = :freq';
            $parameters['freq'] = $params['freq'];
        }

        if (isset($params['proof_type'])) {
            $conditions[] = 'lookup.proof_type = :proof_type';
            $parameters['proof_type'] = $params['proof_type'];
        }

        if (isset($params['glitch'])) {
            $conditions[] = 'lookup.glitch = :glitch';
            $parameters['glitch'] = $params['glitch'];
        }

        $orderField = 'lookup.score';
        if (isset($params['max_value']) && $params['max_value'] === 'damage') {
            $orderField = 'lookup.damage';
            $conditions[] = 'lookup.damage IS NOT NULL';
        }

        $whereClause = implode(' AND ', $conditions);

        $sql = "
            SELECT s.*
            FROM scores s
            WHERE s.id = (
                SELECT lookup.id
                FROM scores lookup
                WHERE lookup.zone_id = s.zone_id
                  AND {$whereClause}
                ORDER BY {$orderField} DESC
                LIMIT 1
            )
            ORDER BY s.zone_id ASC
        ";

        $result = $conn->executeQuery($sql, $parameters);
        $scoreIds = array_column($result->fetchAllAssociative(), 'id');

        if (empty($scoreIds)) {
            return [];
        }

        /** @var list<Score> $scores */
        $scores = $this->createQueryBuilder('s')
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

        return $scores;
    }

    /**
     * Get best multi videos for each zone
     *
     * @return list<Score>
     */
    public function getBestMultiVideos(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT s.*
            FROM scores s
            WHERE s.id = (
                SELECT lookup.id
                FROM scores lookup
                WHERE lookup.zone_id = s.zone_id
                  AND lookup.proof_link IS NOT NULL
                  AND lookup.proof_type IN ('Replay', 'Live')
                  AND lookup.multi IS NOT NULL
                ORDER BY lookup.multi DESC
                LIMIT 1
            )
            ORDER BY s.zone_id ASC
        ";

        $result = $conn->executeQuery($sql);
        $scoreIds = array_column($result->fetchAllAssociative(), 'id');

        if (empty($scoreIds)) {
            return [];
        }

        /** @var list<Score> $scores */
        $scores = $this->createQueryBuilder('s')
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

        return $scores;
    }

    /**
     * Find scores with optional filters for admin list
     *
     * @param array{player?: Player, zone?: Zone} $filters
     * @return list<Score>
     */
    public function findByFilters(array $filters): array
    {
        /** @var list<Score> $result */
        $result = $this->findByFiltersQueryBuilder($filters)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Get QueryBuilder for scores with optional filters (for pagination)
     *
     * @param array{player?: Player, zone?: Zone} $filters
     */
    public function findByFiltersQueryBuilder(array $filters): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.player', 'p')
            ->addSelect('p')
            ->leftJoin('s.zone', 'z')
            ->addSelect('z')
            ->leftJoin('s.car', 'c')
            ->addSelect('c')
            ->leftJoin('s.strat', 'st')
            ->addSelect('st');

        if (isset($filters['player'])) {
            $qb->andWhere('s.player = :player')
               ->setParameter('player', $filters['player']);
        }

        if (isset($filters['zone'])) {
            $qb->andWhere('s.zone = :zone')
               ->setParameter('zone', $filters['zone']);
        }

        $qb->orderBy('z.id', 'ASC')
           ->addOrderBy('s.score', 'DESC');

        return $qb;
    }
}
