<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CarRepository;
use App\Repository\PlayerRepository;
use App\Repository\ScoreRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class PlayerController extends AbstractController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
        private readonly ScoreRepository $scoreRepository,
        private readonly CarRepository $carRepository,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    #[Route('/players', name: 'players_list')]
    public function list(): Response
    {
        $players = $this->playerRepository->findAllOrderedByName();

        return $this->render('player/list.html.twig', [
            'players' => $players,
        ]);
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

    #[Route('/player-id/{id}', name: 'player_redirect', requirements: ['id' => '\d+'])]
    public function redirectById(int $id): Response
    {
        $player = $this->playerRepository->find($id);

        if ($player === null) {
            throw new NotFoundHttpException('Player not found');
        }

        return $this->redirectToRoute('player_view', [
            'nameUrl' => $player->getNameUrl(),
        ]);
    }

    #[Route('/player/{nameUrl}/info', name: 'player_info')]
    public function info(string $nameUrl): Response
    {
        $player = $this->playerRepository->findByNameUrl($nameUrl);

        if ($player === null) {
            throw new NotFoundHttpException('Player not found');
        }

        $platforms = $this->playerRepository->getPlayerPlatforms($player->getId() ?? 0);
        $personalRecords = $this->scoreRepository->findPersonalRecords($player);

        // Initialize statistics arrays
        $proofsCount = ['Replay' => 0, 'Live' => 0, 'XBL' => 0, 'Pic' => 0, 'Unproven' => 0];
        $platformsCount = ['GC' => 0, 'Xbox' => 0, 'PS2' => 0];
        $topsCount = ['3' => 0, '10' => 0, '20' => 0, '25' => 0];
        $podiumCount = ['1' => 0, '2' => 0, '3' => 0];

        // Get all cars and initialize count
        $carList = $this->carRepository->getCarList();
        ksort($carList);
        $carsCount = [];
        foreach ($carList as $carName) {
            $carsCount[$carName] = 0;
        }

        // Calculate statistics from personal records
        foreach ($personalRecords as $pr) {
            // Proof type count
            $proofType = $pr->getProofType();
            if ($proofType === null) {
                $proofsCount['Unproven']++;
            } else {
                $proofTypeName = $proofType->value;
                if (isset($proofsCount[$proofTypeName])) {
                    $proofsCount[$proofTypeName]++;
                }
            }

            // Car count
            $car = $pr->getCar();
            if ($car !== null) {
                $carName = $car->getName();
                if (isset($carsCount[$carName])) {
                    $carsCount[$carName]++;
                }
            }

            // Platform count
            $platform = $pr->getPlatform();
            if ($platform !== null) {
                $platformName = $platform->value;
                $platformsCount[$platformName]++;
            }

            // Ranks count
            $chartRank = $pr->getChartRank();
            if ($chartRank !== null && $chartRank <= 25) {
                $topsCount['25']++;
                if ($chartRank <= 20) {
                    $topsCount['20']++;
                    if ($chartRank <= 10) {
                        $topsCount['10']++;
                        if ($chartRank <= 3) {
                            $topsCount['3']++;
                            $podiumCount[(string) $chartRank]++;
                        }
                    }
                }
            }
        }

        return $this->render('player/info.html.twig', [
            'player' => $player,
            'platforms' => $platforms,
            'proofs' => $proofsCount,
            'cars' => $carsCount,
            'platforms_count' => $platformsCount,
            'tops' => $topsCount,
            'podiums' => $podiumCount,
        ]);
    }

    #[Route('/player/{nameUrl}/lastadded', name: 'player_lastadded')]
    public function lastadded(string $nameUrl, Request $request): Response
    {
        $player = $this->playerRepository->findByNameUrl($nameUrl);

        if ($player === null) {
            throw new NotFoundHttpException('Player not found');
        }

        $platforms = $this->playerRepository->getPlayerPlatforms($player->getId() ?? 0);

        $query = $this->scoreRepository->getLastAddedQuery($player);
        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render('player/lastadded.html.twig', [
            'player' => $player,
            'platforms' => $platforms,
            'latests' => $pagination,
        ]);
    }

    #[Route('/player/{nameUrl}/lastachieved', name: 'player_lastachieved')]
    public function lastachieved(string $nameUrl, Request $request): Response
    {
        $player = $this->playerRepository->findByNameUrl($nameUrl);

        if ($player === null) {
            throw new NotFoundHttpException('Player not found');
        }

        $platforms = $this->playerRepository->getPlayerPlatforms($player->getId() ?? 0);

        $query = $this->scoreRepository->getLastAchievedQuery($player);
        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render('player/lastachieved.html.twig', [
            'player' => $player,
            'platforms' => $platforms,
            'latests' => $pagination,
        ]);
    }
}
