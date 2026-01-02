<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ScoreRepository;
use App\Repository\StratRepository;
use App\Repository\ZoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ZoneController extends AbstractController
{
    public function __construct(
        private readonly ZoneRepository $zoneRepository,
        private readonly ScoreRepository $scoreRepository,
        private readonly StratRepository $stratRepository,
    ) {
    }

    #[Route('/zones', name: 'zones')]
    public function all(): Response
    {
        $zones = $this->zoneRepository->findAll();
        $wrs = $this->scoreRepository->getCurrentWorldRecords();

        return $this->render('zone/all.html.twig', [
            'zones' => $zones,
            'wrs' => $wrs,
        ]);
    }

    #[Route('/zone{id}', name: 'zone', requirements: ['id' => '\d+'])]
    public function index(int $id): Response
    {
        $zone = $this->zoneRepository->findWithStars($id);
        if ($zone === null) {
            return $this->redirectToRoute('zones');
        }

        $topscores = $this->scoreRepository->getTopScores($zone, 25);
        $ngs = $this->scoreRepository->getUnSortedScores($zone, 'None', 10);
        $freezes = $this->scoreRepository->getFreezeScores($zone);
        $sinks = $this->scoreRepository->getUnSortedScores($zone, 'Sink', 10);

        return $this->render('zone/index.html.twig', [
            'zone' => $zone,
            'topscores' => $topscores,
            'ngs' => $ngs,
            'freezes' => $freezes,
            'sinks' => $sinks,
        ]);
    }

    #[Route('/zone{id}/info', name: 'zone_info', requirements: ['id' => '\d+'])]
    public function info(int $id): Response
    {
        $zone = $this->zoneRepository->findWithStars($id);
        if ($zone === null) {
            return $this->redirectToRoute('zones');
        }

        $wrs = $this->scoreRepository->getFormerWorldRecords($zone);

        return $this->render('zone/info.html.twig', [
            'zone' => $zone,
            'wrs' => $wrs,
        ]);
    }

    #[Route('/zone{id}/strats', name: 'zone_strats', requirements: ['id' => '\d+'])]
    public function strats(int $id): Response
    {
        $zone = $this->zoneRepository->find($id);
        if ($zone === null) {
            return $this->redirectToRoute('zones');
        }

        $strats = $this->stratRepository->findByZoneWithScores($zone);

        return $this->render('zone/strats.html.twig', [
            'zone' => $zone,
            'strats' => $strats,
        ]);
    }

    #[Route('/zone{id}/videos', name: 'zone_videos', requirements: ['id' => '\d+'])]
    public function videos(int $id): Response
    {
        $zone = $this->zoneRepository->find($id);
        if ($zone === null) {
            return $this->redirectToRoute('zones');
        }

        $scoreVids = $this->scoreRepository->getBestScoreVideos($zone, 10);
        $damageVids = $this->scoreRepository->getBestDamageVideos($zone, 10);
        $typeVids = $this->scoreRepository->getBestVideosByType($zone);

        return $this->render('zone/videos.html.twig', [
            'zone' => $zone,
            'score_vids' => $scoreVids,
            'damage_vids' => $damageVids,
            'type_vids' => $typeVids,
        ]);
    }
}
