<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Player;
use App\Form\PlayerType;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/players')]
#[IsGranted('ROLE_ADMIN')]
class PlayerController extends AbstractController
{
    #[Route('', name: 'admin_player_list', methods: ['GET'])]
    public function list(Request $request, PlayerRepository $playerRepository, PaginatorInterface $paginator): Response
    {
        $query = $playerRepository->findAllOrderedByNameQueryBuilder()->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/player/list.html.twig', [
            'players' => $pagination,
        ]);
    }

    #[Route('/add', name: 'admin_player_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $player = new Player();
        $form = $this->createForm(PlayerType::class, $player);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($player);
            $entityManager->flush();

            $this->addFlash('success', 'Player created successfully.');

            return $this->redirectToRoute('admin_player_list');
        }

        return $this->render('admin/player/form.html.twig', [
            'form' => $form,
            'player' => $player,
            'is_edit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_player_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Player $player, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlayerType::class, $player);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Player updated successfully.');

            return $this->redirectToRoute('admin_player_list');
        }

        return $this->render('admin/player/form.html.twig', [
            'form' => $form,
            'player' => $player,
            'is_edit' => true,
        ]);
    }
}
