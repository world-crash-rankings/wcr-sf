<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CountryRepository;
use App\Repository\PlayerRepository;
use App\Repository\ScoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class CountryController extends AbstractController
{
    public function __construct(
        private readonly CountryRepository $countryRepository,
        private readonly PlayerRepository $playerRepository,
        private readonly ScoreRepository $scoreRepository,
    ) {
    }

    #[Route('/country/{nameUrl}', name: 'country_view', defaults: ['nameUrl' => 'Germany'])]
    public function view(string $nameUrl): Response
    {
        $country = $this->countryRepository->findByNameUrl($nameUrl);

        if ($country === null) {
            throw new NotFoundHttpException('Country not found');
        }

        $countryId = $country->getId();
        if ($countryId === null) {
            throw new NotFoundHttpException('Invalid country');
        }

        $countries = $this->countryRepository->findAllOrderedByName();
        $players = $this->playerRepository->findByCountryOrderedByRank($countryId);
        $nationalRecords = $this->scoreRepository->getNationalRecords($countryId);

        return $this->render('country/view.html.twig', [
            'country' => $country,
            'countries' => $countries,
            'players' => $players,
            'nationalRecords' => $nationalRecords,
        ]);
    }
}
