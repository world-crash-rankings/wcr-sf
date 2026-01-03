<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PlayerRepository;
use App\Repository\ScoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class PlayerController extends AbstractController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly ScoreRepository $scoreRepository,
    ) {
    }

    #[Route('/player/{nameUrl}', name: 'player_view')]
    public function view(string $nameUrl): Response
    {
        $player = $this->playerRepository->findByNameUrl($nameUrl);

        if ($player === null) {
            throw new NotFoundHttpException('Player not found');
        }

        $platforms = $this->playerRepository->getPlayerPlatforms($player->getId() ?? 0);
        $personalRecords = $this->scoreRepository->findPersonalRecords($player);

        return $this->render('player/view.html.twig', [
            'player' => $player,
            'platforms' => $platforms,
            'personalRecords' => $personalRecords,
        ]);
    }
}
