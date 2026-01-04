<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Strat;
use App\Form\StratType;
use App\Repository\StratRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/strats')]
#[IsGranted('ROLE_ADMIN')]
class StratController extends AbstractController
{
    #[Route('', name: 'admin_strat_list', methods: ['GET'])]
    public function list(
        Request $request,
        StratRepository $stratRepository,
        PaginatorInterface $paginator
    ): Response {
        $query = $stratRepository->findAllOrderedByIdQueryBuilder()->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            30
        );

        return $this->render('admin/strat/list.html.twig', [
            'strats' => $pagination,
        ]);
    }

    #[Route('/add', name: 'admin_strat_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $strat = new Strat();
        $form = $this->createForm(StratType::class, $strat);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($strat);
            $entityManager->flush();

            $this->addFlash('success', 'Strat created successfully.');

            return $this->redirectToRoute('admin_strat_list');
        }

        return $this->render('admin/strat/form.html.twig', [
            'form' => $form,
            'strat' => $strat,
            'is_edit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_strat_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Strat $strat,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(StratType::class, $strat);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Strat updated successfully.');

            return $this->redirectToRoute('admin_strat_list');
        }

        return $this->render('admin/strat/form.html.twig', [
            'form' => $form,
            'strat' => $strat,
            'is_edit' => true,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_strat_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Strat $strat,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $strat->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($strat);
            $entityManager->flush();

            $this->addFlash('success', 'Strat deleted successfully.');
        }

        return $this->redirectToRoute('admin_strat_list');
    }
}
