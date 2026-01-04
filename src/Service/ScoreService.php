<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Player;
use App\Entity\Score;
use App\Entity\Zone;
use App\Enum\GlitchType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service to handle score management and ranking calculations
 * Replaces all MySQL stored procedures with PHP logic
 */
class ScoreService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Add a new score and handle all ranking calculations
     */
    public function addScore(Score $score): void
    {
        // Calculate initial values
        $this->calculateScoreMetrics($score);

        // Persist the score first
        $this->entityManager->persist($score);
        $this->entityManager->flush();

        // Check if this is a personal record
        if ($this->isPersonalRecord($score)) {
            $this->handlePersonalRecord($score);
        }

        // Recalculate player rankings
        $this->updatePlayerStatistics($score->getPlayer());
        $this->updateAllPlayerRankings();

        $this->entityManager->flush();
    }

    /**
     * Update an existing score and recalculate rankings
     */
    public function updateScore(Score $score, string $oldScoreValue, ?GlitchType $oldGlitch): void
    {
        $scoreChanged = $oldScoreValue !== $score->getScore();
        $glitchChanged = $oldGlitch !== $score->getGlitch();
        $affectsRanking = $scoreChanged || $this->glitchAffectsRanking($oldGlitch, $score->getGlitch());

        // Recalculate metrics
        $this->calculateScoreMetrics($score);
        $this->entityManager->flush();

        if ($affectsRanking) {
            // Clear current rankings for this player/zone
            $this->clearPlayerZoneRankings($score->getPlayer(), $score->getZone());

            // Recalculate PR for this player/zone
            $pr = $this->findPlayerPersonalRecord($score->getPlayer(), $score->getZone());
            if ($pr !== null) {
                $this->setPersonalRecordRanking($pr);
            }

            // If WR changed, update all percent_wr for the zone
            $wr = $this->getWorldRecord($score->getZone());
            if ($wr !== null && ($wr->getId() === $score->getId() || $this->wasWorldRecord($score, $oldScoreValue))) {
                $this->updateZonePercentWr($score->getZone());
            }

            // Recalculate player statistics and rankings
            $this->updatePlayerStatistics($score->getPlayer());
            $this->updateAllPlayerRankings();

            $this->entityManager->flush();
        }
    }

    /**
     * Delete a score and recalculate rankings
     */
    public function deleteScore(Score $score): void
    {
        $player = $score->getPlayer();
        $zone = $score->getZone();
        $wasRanked = $score->getChartRank() !== null;
        $wasWorldRecord = $score->getChartRank() === 1;

        // Remove the score
        $this->entityManager->remove($score);
        $this->entityManager->flush();

        if ($wasRanked) {
            // Clear rankings for this player/zone
            $this->clearPlayerZoneRankings($player, $zone);

            // Find new PR for this player/zone
            $pr = $this->findPlayerPersonalRecord($player, $zone);
            if ($pr !== null) {
                $this->setPersonalRecordRanking($pr);
            }

            // If it was WR, update all percent_wr for the zone
            if ($wasWorldRecord) {
                $this->updateZonePercentWr($zone);
            }

            // Recalculate player statistics and rankings
            $this->updatePlayerStatistics($player);
            $this->updateAllPlayerRankings();

            $this->entityManager->flush();
        }
    }

    /**
     * Calculate percent_wr and stars for a score
     */
    private function calculateScoreMetrics(Score $score): void
    {
        $zone = $score->getZone();
        $scoreValue = (int) $score->getScore();

        // Calculate percent WR
        $wr = $this->getWorldRecord($zone);
        if ($wr !== null) {
            $wrValue = (int) $wr->getScore();
            $percentWr = $wrValue > 0 ? ($scoreValue * 100.0) / $wrValue : 100.0;
            $score->setPercentWr(number_format($percentWr, 2, '.', ''));
        } else {
            $score->setPercentWr('100.00');
        }

        // Calculate stars
        $stars = $this->calculateStars($zone, $scoreValue);
        $score->setStars($stars);
    }

    /**
     * Get the number of stars for a score based on zone thresholds
     */
    private function calculateStars(Zone $zone, int $scoreValue): int
    {
        $stars = 0;

        $dql = 'SELECT COUNT(s) FROM App\Entity\Star s
                WHERE s.zone = :zone AND :score > s.score';

        $stars = (int) $this->entityManager->createQuery($dql)
            ->setParameter('zone', $zone)
            ->setParameter('score', $scoreValue)
            ->getSingleScalarResult();

        return $stars;
    }

    /**
     * Get the current world record for a zone
     */
    private function getWorldRecord(Zone $zone): ?Score
    {
        return $this->entityManager->getRepository(Score::class)
            ->findOneBy(
                ['zone' => $zone, 'chartRank' => 1],
                ['score' => 'DESC']
            );
    }

    /**
     * Check if a score is a personal record for the player on this zone
     */
    private function isPersonalRecord(Score $score): bool
    {
        // Freeze and Sink scores cannot be personal records
        if ($this->isNonRankableGlitch($score->getGlitch())) {
            return false;
        }

        $player = $score->getPlayer();
        $zone = $score->getZone();
        $scoreValue = (int) $score->getScore();

        // Check if there's a better or equal rankable score for this player/zone
        $dql = 'SELECT COUNT(s) FROM App\Entity\Score s
                WHERE s.player = :player
                AND s.zone = :zone
                AND s.score >= :score
                AND (s.glitch NOT IN (:nonRankable) OR s.glitch IS NULL)';

        $count = (int) $this->entityManager->createQuery($dql)
            ->setParameter('player', $player)
            ->setParameter('zone', $zone)
            ->setParameter('score', $scoreValue)
            ->setParameter('nonRankable', [GlitchType::FREEZE, GlitchType::SINK])
            ->getSingleScalarResult();

        return $count === 1; // Only this score exists with this value or better
    }

    /**
     * Handle setting up a personal record with chart ranking
     */
    private function handlePersonalRecord(Score $score): void
    {
        $player = $score->getPlayer();
        $zone = $score->getZone();

        // Clear existing chart_rank for this player/zone
        $this->clearPlayerZoneRankings($player, $zone);

        // Set PR flag and calculate rank
        $score->setPrEntry(true);
        $this->setPersonalRecordRanking($score);

        // If this is rank 1, mark as former WR and update all percent_wr
        if ($score->getChartRank() === 1) {
            $score->setFormerWr(true);
            $this->updateZonePercentWr($zone);
        }
    }

    /**
     * Calculate and set the chart rank for a personal record
     */
    private function setPersonalRecordRanking(Score $score): void
    {
        $zone = $score->getZone();
        $scoreValue = (int) $score->getScore();

        // Count how many player PRs have a better score
        $dql = 'SELECT COUNT(DISTINCT s.player) FROM App\Entity\Score s
                WHERE s.zone = :zone
                AND s.chartRank IS NOT NULL
                AND s.score > :score';

        $rank = (int) $this->entityManager->createQuery($dql)
            ->setParameter('zone', $zone)
            ->setParameter('score', $scoreValue)
            ->getSingleScalarResult();

        $rank += 1; // Add 1 for this score's rank

        $score->setChartRank($rank);
        $score->setBestRank($rank);

        // Update ranks of other scores affected
        $this->shiftRanksForNewScore($zone, $scoreValue, $rank);
    }

    /**
     * Shift ranks for scores affected by a new PR
     */
    private function shiftRanksForNewScore(Zone $zone, int $scoreValue, int $newRank): void
    {
        // Increment ranks for scores lower than the new score and with rank >= newRank
        $dql = 'UPDATE App\Entity\Score s
                SET s.chartRank = s.chartRank + 1
                WHERE s.zone = :zone
                AND s.chartRank >= :rank
                AND s.score < :score
                AND s.chartRank IS NOT NULL';

        $this->entityManager->createQuery($dql)
            ->setParameter('zone', $zone)
            ->setParameter('rank', $newRank)
            ->setParameter('score', $scoreValue)
            ->execute();
    }

    /**
     * Clear chart rankings for a player on a specific zone
     */
    private function clearPlayerZoneRankings(Player $player, Zone $zone): void
    {
        $dql = 'UPDATE App\Entity\Score s
                SET s.chartRank = NULL
                WHERE s.player = :player
                AND s.zone = :zone';

        $this->entityManager->createQuery($dql)
            ->setParameter('player', $player)
            ->setParameter('zone', $zone)
            ->execute();
    }

    /**
     * Find the best rankable score for a player on a zone
     */
    private function findPlayerPersonalRecord(Player $player, Zone $zone): ?Score
    {
        $dql = 'SELECT s FROM App\Entity\Score s
                WHERE s.player = :player
                AND s.zone = :zone
                AND (s.glitch NOT IN (:nonRankable) OR s.glitch IS NULL)
                ORDER BY s.score DESC';

        $result = $this->entityManager->createQuery($dql)
            ->setParameter('player', $player)
            ->setParameter('zone', $zone)
            ->setParameter('nonRankable', [GlitchType::FREEZE, GlitchType::SINK])
            ->setMaxResults(1)
            ->getResult();

        return $result[0] ?? null;
    }

    /**
     * Update percent_wr for all scores in a zone
     */
    private function updateZonePercentWr(Zone $zone): void
    {
        $wr = $this->getWorldRecord($zone);
        if ($wr === null) {
            return;
        }

        $wrValue = (int) $wr->getScore();
        if ($wrValue === 0) {
            return;
        }

        // Get all scores for this zone
        $scores = $this->entityManager->getRepository(Score::class)
            ->findBy(['zone' => $zone]);

        foreach ($scores as $score) {
            $scoreValue = (int) $score->getScore();
            $percentWr = ($scoreValue * 100.0) / $wrValue;
            $score->setPercentWr(number_format($percentWr, 2, '.', ''));
        }

        $this->entityManager->flush();
    }

    /**
     * Update player statistics (total, avg_pos, avg_percent, avg_stars)
     */
    private function updatePlayerStatistics(Player $player): void
    {
        // Calculate total
        $total = $this->calculateTotal($player);
        $player->setTotal((string) $total);

        // Calculate avg_pos
        $avgPos = $this->calculateAvgPos($player);
        $player->setAvgPos($avgPos);

        // Calculate avg_percent
        $avgPercent = $this->calculateAvgPercent($player);
        $player->setAvgPercent($avgPercent);

        // Calculate avg_stars
        $avgStars = $this->calculateAvgStars($player);
        $player->setAvgStars($avgStars);
    }

    /**
     * Calculate total score for a player
     */
    private function calculateTotal(Player $player): int
    {
        $dql = 'SELECT SUM(s.score) FROM App\Entity\Score s
                WHERE s.player = :player
                AND s.chartRank IS NOT NULL';

        $total = $this->entityManager->createQuery($dql)
            ->setParameter('player', $player)
            ->getSingleScalarResult();

        $newTotal = (int) ($total ?? 0);

        // Handle xbl_total logic
        if ($player->isXblTotal()) {
            $oldTotal = (int) ($player->getTotal() ?? 0);
            $newTotal = max($newTotal, $oldTotal);
        }

        return $newTotal;
    }

    /**
     * Calculate average position for a player
     */
    private function calculateAvgPos(Player $player): string
    {
        // Sum of ranks for top 25 positions
        $dql = 'SELECT SUM(s.chartRank), COUNT(s.chartRank) FROM App\Entity\Score s
                WHERE s.player = :player
                AND s.chartRank < 26
                AND s.chartRank IS NOT NULL';

        $result = $this->entityManager->createQuery($dql)
            ->setParameter('player', $player)
            ->getSingleResult();

        $sumRanks = (int) ($result[1] ?? 0);
        $nbTop25 = (int) ($result[2] ?? 0);

        // Calculate: (sum_ranks / 30) + (30 - nb_top_25)
        $avgPos = ($sumRanks / 30.000) + (30 - $nbTop25);

        return number_format($avgPos, 3, '.', '');
    }

    /**
     * Calculate average percent WR for a player
     */
    private function calculateAvgPercent(Player $player): string
    {
        $dql = 'SELECT SUM(s.percentWr) FROM App\Entity\Score s
                WHERE s.player = :player
                AND s.chartRank IS NOT NULL';

        $sumPercent = $this->entityManager->createQuery($dql)
            ->setParameter('player', $player)
            ->getSingleScalarResult();

        $avgPercent = ((float) ($sumPercent ?? 0)) / 30.0;

        return number_format($avgPercent, 2, '.', '');
    }

    /**
     * Calculate average stars for a player
     */
    private function calculateAvgStars(Player $player): string
    {
        $dql = 'SELECT SUM(s.stars) FROM App\Entity\Score s
                WHERE s.player = :player
                AND s.chartRank IS NOT NULL';

        $sumStars = $this->entityManager->createQuery($dql)
            ->setParameter('player', $player)
            ->getSingleScalarResult();

        $avgStars = ((float) ($sumStars ?? 0)) / 30.0;

        return number_format($avgStars, 1, '.', '');
    }

    /**
     * Update rankings for all players
     */
    private function updateAllPlayerRankings(): void
    {
        $players = $this->entityManager->getRepository(Player::class)->findAll();

        foreach ($players as $player) {
            $player->setTotalRank($this->calculateTotalRank($player));
            $player->setAvgPosRank($this->calculateAvgPosRank($player));
            $player->setAvgPercentRank($this->calculateAvgPercentRank($player));
            $player->setAvgStarsRank($this->calculateAvgStarsRank($player));
        }
    }

    /**
     * Calculate total rank for a player
     */
    private function calculateTotalRank(Player $player): int
    {
        $total = (int) ($player->getTotal() ?? 0);

        $dql = 'SELECT COUNT(p) + 1 FROM App\Entity\Player p
                WHERE p.total > :total';

        return (int) $this->entityManager->createQuery($dql)
            ->setParameter('total', (string) $total)
            ->getSingleScalarResult();
    }

    /**
     * Calculate avg position rank for a player
     */
    private function calculateAvgPosRank(Player $player): int
    {
        $avgPos = $player->getAvgPos() ?? '999.999';

        $dql = 'SELECT COUNT(p) + 1 FROM App\Entity\Player p
                WHERE p.avgPos < :avgPos';

        return (int) $this->entityManager->createQuery($dql)
            ->setParameter('avgPos', $avgPos)
            ->getSingleScalarResult();
    }

    /**
     * Calculate avg percent rank for a player
     */
    private function calculateAvgPercentRank(Player $player): int
    {
        $avgPercent = $player->getAvgPercent() ?? '0.00';

        $dql = 'SELECT COUNT(p) + 1 FROM App\Entity\Player p
                WHERE p.avgPercent > :avgPercent';

        return (int) $this->entityManager->createQuery($dql)
            ->setParameter('avgPercent', $avgPercent)
            ->getSingleScalarResult();
    }

    /**
     * Calculate avg stars rank for a player
     */
    private function calculateAvgStarsRank(Player $player): int
    {
        $avgStars = $player->getAvgStars() ?? '0.0';

        $dql = 'SELECT COUNT(p) + 1 FROM App\Entity\Player p
                WHERE p.avgStars > :avgStars';

        return (int) $this->entityManager->createQuery($dql)
            ->setParameter('avgStars', $avgStars)
            ->getSingleScalarResult();
    }

    /**
     * Check if a glitch type is non-rankable
     */
    private function isNonRankableGlitch(?GlitchType $glitch): bool
    {
        return $glitch === GlitchType::FREEZE || $glitch === GlitchType::SINK;
    }

    /**
     * Check if glitch change affects ranking
     */
    private function glitchAffectsRanking(?GlitchType $oldGlitch, ?GlitchType $newGlitch): bool
    {
        $oldNonRankable = $this->isNonRankableGlitch($oldGlitch);
        $newNonRankable = $this->isNonRankableGlitch($newGlitch);

        return $oldNonRankable !== $newNonRankable;
    }

    /**
     * Check if a score was the world record before update
     */
    private function wasWorldRecord(Score $score, string $oldScoreValue): bool
    {
        // If the score was rank 1 and the value changed, it might have been WR
        return $score->getChartRank() === 1 && $oldScoreValue !== $score->getScore();
    }
}
