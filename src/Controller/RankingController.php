<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\RankingLimitType;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rankings')]
class RankingController extends AbstractController
{
    public function __construct(
        private readonly PlayerRepository $playerRepository,
    ) {
    }

    #[Route('/total/{limit}', name: 'rankings_total', requirements: ['limit' => '\d+'], defaults: ['limit' => 25])]
    public function total(Request $request, int $limit = 25): Response
    {
        $form = $this->createForm(RankingLimitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute('rankings_total', ['limit' => $data['limit'] ?? 25]);
        }

        $players = $this->playerRepository->findByTotalRank($limit);

        return $this->render('ranking/total.html.twig', [
            'players' => $players,
            'form' => $form,
        ]);
    }

    #[Route('/ap/{limit}', name: 'rankings_ap', requirements: ['limit' => '\d+'], defaults: ['limit' => 25])]
    public function ap(Request $request, int $limit = 25): Response
    {
        $form = $this->createForm(RankingLimitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute('rankings_ap', ['limit' => $data['limit'] ?? 25]);
        }

        $players = $this->playerRepository->findByAvgPosRank($limit);

        return $this->render('ranking/ap.html.twig', [
            'players' => $players,
            'form' => $form,
        ]);
    }

    #[Route('/stars/{limit}', name: 'rankings_stars', requirements: ['limit' => '\d+'], defaults: ['limit' => 25])]
    public function stars(Request $request, int $limit = 25): Response
    {
        $form = $this->createForm(RankingLimitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute('rankings_stars', ['limit' => $data['limit'] ?? 25]);
        }

        $players = $this->playerRepository->findByAvgStarsRank($limit);

        return $this->render('ranking/stars.html.twig', [
            'players' => $players,
            'form' => $form,
        ]);
    }

    #[Route('/percent/{limit}', name: 'rankings_percent', requirements: ['limit' => '\d+'], defaults: ['limit' => 25])]
    public function percent(Request $request, int $limit = 25): Response
    {
        $form = $this->createForm(RankingLimitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute('rankings_percent', ['limit' => $data['limit'] ?? 25]);
        }

        $players = $this->playerRepository->findByAvgPercentRank($limit);

        return $this->render('ranking/percent.html.twig', [
            'players' => $players,
            'form' => $form,
        ]);
    }
}
