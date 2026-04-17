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
    name: 'app:fix-pr-ranks',
    description: 'Fix missing chart_rank for personal record entries'
)]
class FixPrRanksCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be fixed without making changes')
            ->addOption('zone', 'z', InputOption::VALUE_OPTIONAL, 'Fix only specific zone ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $zoneFilter = $input->getOption('zone');

        $io->title('Fix Missing Chart Ranks for Personal Records');

        if ($dryRun) {
            $io->note('Running in DRY-RUN mode - no changes will be made');
        }

        // Find all PR entries without chart_rank
        $whereClause = 's.pr_entry = 1 AND s.chart_rank IS NULL';
        if ($zoneFilter !== null && is_numeric($zoneFilter)) {
            $whereClause .= ' AND s.zone_id = ' . (int) $zoneFilter;
        }

        $sql = "SELECT s.id, s.player_id, s.zone_id, s.score, p.name as player_name, z.name as zone_name 
                FROM scores s 
                LEFT JOIN players p ON s.player_id = p.id 
                LEFT JOIN zones z ON s.zone_id = z.id 
                WHERE {$whereClause}
                ORDER BY s.zone_id, s.score DESC";

        $missingRanks = $this->connection->fetchAllAssociative($sql);

        if (empty($missingRanks)) {
            $io->success('No missing chart ranks found!');
            return Command::SUCCESS;
        }

        $io->section(sprintf('Found %d scores with missing chart_rank', count($missingRanks)));

        $fixedCount = 0;
        $currentZone = null;
        $zoneScores = [];

        foreach ($missingRanks as $score) {
            // Group by zone to calculate ranks efficiently
            $scoreZoneId = is_numeric($score['zone_id']) ? (int) $score['zone_id'] : 0;
            if ($currentZone !== $scoreZoneId) {
                if ($currentZone !== null) {
                    $fixedCount += $this->fixZoneRanks($currentZone, $zoneScores, $dryRun, $io);
                }
                $currentZone = $scoreZoneId;
                $zoneScores = [];
            }

            $zoneScores[] = [
                'id' => is_numeric($score['id']) ? (int) $score['id'] : 0,
                'player_id' => is_numeric($score['player_id']) ? (int) $score['player_id'] : 0,
                'zone_id' => is_numeric($score['zone_id']) ? (int) $score['zone_id'] : 0,
                'score' => is_numeric($score['score']) ? (int) $score['score'] : 0,
                'player_name' => is_string($score['player_name']) ? $score['player_name'] : '',
                'zone_name' => is_string($score['zone_name']) ? $score['zone_name'] : '',
            ];
        }

        // Fix the last zone
        if ($currentZone > 0) {
            $fixedCount += $this->fixZoneRanks($currentZone, $zoneScores, $dryRun, $io);
        }

        if ($dryRun) {
            $io->success(sprintf('Would fix %d scores', $fixedCount));
            $io->note('Run without --dry-run to apply changes');
        } else {
            $io->success(sprintf('Fixed %d scores', $fixedCount));
        }

        return Command::SUCCESS;
    }

    /**
     * Fix ranks for a specific zone
     * @param array<array{id: int, player_id: int, zone_id: int, score: int, player_name: string, zone_name: string}> $zoneScores
     */
    private function fixZoneRanks(int $zoneId, array $zoneScores, bool $dryRun, SymfonyStyle $io): int
    {
        if (empty($zoneScores)) {
            return 0;
        }

        $io->section(sprintf('Processing Zone %d (%s)', $zoneId, $zoneScores[0]['zone_name']));

        $fixedCount = 0;

        foreach ($zoneScores as $scoreData) {
            // Calculate rank: count how many distinct players have a better PR score in this zone
            $rankSql = "SELECT COUNT(DISTINCT s2.player_id) + 1 as calculated_rank
                        FROM scores s2 
                        WHERE s2.zone_id = :zone_id 
                        AND s2.pr_entry = 1 
                        AND s2.score > :score";

            $result = $this->connection->fetchOne($rankSql, [
                'zone_id' => $zoneId,
                'score' => $scoreData['score']
            ]);
            $rank = is_numeric($result) ? (int) $result : 1;

            $io->text(sprintf(
                '  %s (Score: %s) -> Rank #%d',
                $scoreData['player_name'],
                number_format($scoreData['score']),
                $rank
            ));

            if (!$dryRun) {
                // Update the score with calculated rank
                $this->connection->executeStatement(
                    'UPDATE scores SET chart_rank = :rank, best_rank = :rank WHERE id = :id',
                    [
                        'rank' => $rank,
                        'id' => $scoreData['id']
                    ]
                );
            }

            $fixedCount++;
        }

        return $fixedCount;
    }
}
