<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ScoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VideoController extends AbstractController
{
    public function __construct(
        private readonly ScoreRepository $scoreRepository,
    ) {
    }

    #[Route('/videos/{type}', name: 'videos', defaults: ['type' => 'score'])]
    public function index(string $type): Response
    {
        $playlists = [
            'score' => ['title' => 'Best Scores', 'info' => 'Best available video for each zone'],
            'nonglitch' => ['title' => 'Non Glitch', 'info' => 'Best available non-glitch video for each zone'],
            'damage' => ['title' => 'Highest Damage', 'info' => 'Highest known damage video for each zone'],
            'multi' => ['title' => 'Highest Multi', 'info' => 'Highest known multi video for each zone'],
            'live' => ['title' => 'Live', 'info' => 'Best available live recorded video for each zone'],
            'ps2' => [
                'title' => 'PS2',
                'info' => 'Best available video for each zone done on the Playstation 2',
            ],
            'xbox50' => [
                'title' => 'Xbox 50Hz',
                'info' => 'Best available video for each zone done on the Xbox in 50Hz mode',
            ],
            'xbox60' => [
                'title' => 'Xbox 60Hz',
                'info' => 'Best available video for each zone done on the Xbox in 60Hz mode',
            ],
            'gc50' => [
                'title' => 'GC 50Hz',
                'info' => 'Best available video for each zone done on the Gamecube in 50Hz mode',
            ],
            'gc60' => [
                'title' => 'GC 60Hz',
                'info' => 'Best available video for each zone done on the Gamecube in 60Hz mode',
            ],
        ];

        if (!isset($playlists[$type])) {
            throw $this->createNotFoundException('Invalid video type');
        }

        $videos = match ($type) {
            'score' => $this->scoreRepository->getBestVideos([]),
            'nonglitch' => $this->scoreRepository->getBestVideos(['glitch' => 'None']),
            'damage' => $this->scoreRepository->getBestVideos(['max_value' => 'damage']),
            'multi' => $this->scoreRepository->getBestMultiVideos(),
            'live' => $this->scoreRepository->getBestVideos(['proof_type' => 'Live']),
            'ps2' => $this->scoreRepository->getBestVideos(['platform' => 'PS2']),
            'xbox50' => $this->scoreRepository->getBestVideos(['platform' => 'Xbox', 'freq' => '50Hz']),
            'xbox60' => $this->scoreRepository->getBestVideos(['platform' => 'Xbox', 'freq' => '60Hz']),
            'gc50' => $this->scoreRepository->getBestVideos(['platform' => 'GC', 'freq' => '50Hz']),
            'gc60' => $this->scoreRepository->getBestVideos(['platform' => 'GC', 'freq' => '60Hz']),
        };

        // Calculate statistics
        $totalScore = 0;
        $playerCounts = [];
        $platformCounts = [];
        $freqCounts = [];

        foreach ($videos as $video) {
            $totalScore += (int) $video->getScore();

            // Count by player
            $playerId = $video->getPlayer()->getId();
            if (!isset($playerCounts[$playerId])) {
                $playerCounts[$playerId] = [
                    'player' => $video->getPlayer(),
                    'count' => 0,
                ];
            }
            $playerCounts[$playerId]['count']++;

            // Count by platform
            if ($video->getPlatform()) {
                $platform = $video->getPlatform()->value;
                $platformCounts[$platform] = ($platformCounts[$platform] ?? 0) + 1;
            }

            // Count by frequency
            if ($video->getFreq()) {
                $freq = $video->getFreq()->value;
                $freqCounts[$freq] = ($freqCounts[$freq] ?? 0) + 1;
            }
        }

        // Sort player counts by count descending
        usort($playerCounts, fn($a, $b) => $b['count'] <=> $a['count']);

        return $this->render('video/index.html.twig', [
            'videos' => $videos,
            'type' => $type,
            'playlists' => $playlists,
            'title' => $playlists[$type]['title'],
            'info' => $playlists[$type]['info'],
            'totalScore' => $totalScore,
            'playerCounts' => $playerCounts,
            'platformCounts' => $platformCounts,
            'freqCounts' => $freqCounts,
        ]);
    }
}
