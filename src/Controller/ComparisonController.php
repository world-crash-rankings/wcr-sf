<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\PlayerComparisonType;
use App\Repository\PlayerRepository;
use App\Repository\ScoreRepository;
use App\Repository\ZoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ComparisonController extends AbstractController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly ScoreRepository $scoreRepository,
        private readonly ZoneRepository $zoneRepository,
    ) {
    }

    #[Route(
        '/vs/{nameUrl1}/{nameUrl2}',
        name: 'player_comparison',
        requirements: ['nameUrl1' => '[^/]+', 'nameUrl2' => '[^/]+']
    )]
    public function compare(Request $request, ?string $nameUrl1 = null, ?string $nameUrl2 = null): Response
    {
        $form = $this->createForm(PlayerComparisonType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data !== null && isset($data['player1'], $data['player2'])) {
                return $this->redirectToRoute('player_comparison', [
                    'nameUrl1' => $data['player1'],
                    'nameUrl2' => $data['player2'],
                ]);
            }
        }

        // If no players selected, show form only
        if ($nameUrl1 === null || $nameUrl2 === null) {
            return $this->render('comparison/index.html.twig', [
                'form' => $form,
            ]);
        }

        // Load both players
        $player1 = $this->playerRepository->findByNameUrl($nameUrl1);
        $player2 = $this->playerRepository->findByNameUrl($nameUrl2);

        if ($player1 === null || $player2 === null) {
            $this->addFlash('error', 'One or both players not found.');
            return $this->redirectToRoute('player_comparison');
        }

        // Load data for both players
        $platforms1 = $this->playerRepository->getPlayerPlatforms($player1->getId() ?? 0);
        $platforms2 = $this->playerRepository->getPlayerPlatforms($player2->getId() ?? 0);
        $personalRecords1 = $this->scoreRepository->findPersonalRecords($player1);
        $personalRecords2 = $this->scoreRepository->findPersonalRecords($player2);
        $zones = $this->zoneRepository->findAll();

        // Pre-fill form with current players
        $form->setData([
            'player1' => $nameUrl1,
            'player2' => $nameUrl2,
        ]);

        return $this->render('comparison/index.html.twig', [
            'form' => $form,
            'player1' => $player1,
            'player2' => $player2,
            'platforms1' => $platforms1,
            'platforms2' => $platforms2,
            'personalRecords1' => $personalRecords1,
            'personalRecords2' => $personalRecords2,
            'zones' => $zones,
        ]);
    }
}
