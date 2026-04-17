<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:recalculate-ranks',
    description: 'Recalculate chart ranks - only one rank per player per zone (their best score)'
)]
class RecalculateRanksCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be done without making changes')
            ->addOption('zone', 'z', InputOption::VALUE_OPTIONAL, 'Recalculate only specific zone ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $zoneFilter = $input->getOption('zone');

        $io->title('Recalculate Chart Ranks');

        if ($dryRun) {
            $io->note('Running in DRY-RUN mode - no changes will be made');
        }

        // Step 1: Clear all chart_rank and best_rank
        $whereClause = '1=1';
        if ($zoneFilter !== null && is_numeric($zoneFilter)) {
            $whereClause = 'zone_id = ' . (int) $zoneFilter;
        }

        if (!$dryRun) {
            $cleared = $this->connection->executeStatement(
                "UPDATE scores SET chart_rank = NULL, best_rank = NULL WHERE {$whereClause}"
            );
            $io->text("Cleared {$cleared} existing ranks");
        } else {
            $result = $this->connection->fetchOne(
                "SELECT COUNT(*) FROM scores WHERE chart_rank IS NOT NULL AND {$whereClause}"
            );
            $count = is_numeric($result) ? (int) $result : 0;
            $io->text("Would clear {$count} existing ranks");
        }

        // Step 2: Get best score per player per zone
        $sql = "SELECT 
                    s.id, 
                    s.player_id, 
                    s.zone_id, 
                    s.score, 
                    s.pr_entry,
                    p.name as player_name,
                    z.name as zone_name
                FROM scores s
                JOIN players p ON s.player_id = p.id
                JOIN zones z ON s.zone_id = z.id
                JOIN (
                    SELECT player_id, zone_id, MAX(score) as best_score
                    FROM scores 
                    WHERE {$whereClause}
                    GROUP BY player_id, zone_id
                ) best ON s.player_id = best.player_id 
                         AND s.zone_id = best.zone_id 
                         AND s.score = best.best_score
                ORDER BY s.zone_id, s.score DESC";

        $bestScores = $this->connection->fetchAllAssociative($sql);

        if (empty($bestScores)) {
            $io->success('No scores found to rank!');
            return Command::SUCCESS;
        }

        $io->section(sprintf('Found %d best scores to rank', count($bestScores)));

        $rankedCount = 0;
        $currentZone = null;
        $currentRank = 1;

        foreach ($bestScores as $scoreData) {
            // Reset rank counter for each zone
            $scoreZoneId = is_numeric($scoreData['zone_id']) ? (int) $scoreData['zone_id'] : 0;
            if ($currentZone !== $scoreZoneId) {
                if ($currentZone !== null) {
                    $io->text('');
                }
                $currentZone = $scoreZoneId;
                $currentRank = 1;
                $zoneName = is_string($scoreData['zone_name']) ? $scoreData['zone_name'] : 'Unknown';
                $io->section("Zone {$currentZone} ({$zoneName})");
            }

            $playerName = is_string($scoreData['player_name']) ? $scoreData['player_name'] : 'Unknown';
            $score = is_numeric($scoreData['score']) ? (int) $scoreData['score'] : 0;
            $prEntry = (bool) $scoreData['pr_entry'];

            $io->text(sprintf(
                '  #%d - %s (Score: %s) %s',
                $currentRank,
                $playerName,
                number_format($score),
                $prEntry ? '[PR]' : ''
            ));

            if (!$dryRun) {
                $scoreId = is_numeric($scoreData['id']) ? (int) $scoreData['id'] : 0;
                if ($scoreId > 0) {
                    $this->connection->executeStatement(
                        'UPDATE scores SET chart_rank = :rank, best_rank = :rank WHERE id = :id',
                        [
                            'rank' => $currentRank,
                            'id' => $scoreId
                        ]
                    );
                }
            }

            $currentRank++;
            $rankedCount++;
        }

        if ($dryRun) {
            $io->success(sprintf('Would rank %d scores', $rankedCount));
            $io->note('Run without --dry-run to apply changes');
        } else {
            $io->success(sprintf('Successfully ranked %d scores', $rankedCount));
        }

        return Command::SUCCESS;
    }
}
