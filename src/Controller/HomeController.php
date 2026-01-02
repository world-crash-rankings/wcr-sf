<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\NewsRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, NewsRepository $newsRepository, PaginatorInterface $paginator): Response
    {
        $query = $newsRepository->findAllOrderedByDateQueryBuilder()->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('home/index.html.twig', [
            'news' => $pagination,
        ]);
    }
}
