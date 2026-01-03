<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ScoreRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ScoreController extends AbstractController
{
    public function __construct(
        private readonly ScoreRepository $scoreRepository,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    #[Route('/scores/lastadded', name: 'scores_lastadded')]
    public function lastadded(Request $request): Response
    {
        $query = $this->scoreRepository->getLastAddedScoresQuery();
        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            25
        );

        return $this->render('score/lastadded.html.twig', [
            'scores' => $pagination,
        ]);
    }

    #[Route('/scores/lastachieved', name: 'scores_lastachieved')]
    public function lastachieved(Request $request): Response
    {
        $query = $this->scoreRepository->getLastAchievedScoresQuery();
        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            25
        );

        return $this->render('score/lastachieved.html.twig', [
            'scores' => $pagination,
        ]);
    }
}
