<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Score;
use App\Entity\Zone;
use App\Form\ScoreSearchType;
use App\Form\ScoreType;
use App\Repository\ScoreRepository;
use App\Repository\ZoneRepository;
use App\Service\ScoreService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/scores')]
#[IsGranted('ROLE_ADMIN')]
class ScoreController extends AbstractController
{
    public function __construct(
        private readonly ScoreService $scoreService,
    ) {
    }

    #[Route('', name: 'admin_score_list', methods: ['GET', 'POST'])]
    public function list(
        Request $request,
        ScoreRepository $scoreRepository,
        ZoneRepository $zoneRepository,
        PaginatorInterface $paginator
    ): Response {
        $searchForm = $this->createForm(ScoreSearchType::class);
        $searchForm->handleRequest($request);

        $scores = null;
        $searched = false;

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $filters = [];
            /** @var array{player: \App\Entity\Player|null, zone: \App\Entity\Zone|null}|null $data */
            $data = $searchForm->getData();

            if ($data !== null) {
                if ($data['player'] !== null) {
                    $filters['player'] = $data['player'];
                }

                if ($data['zone'] !== null) {
                    $filters['zone'] = $data['zone'];
                }
            }

            // Always search when form is submitted, even without filters
            $query = $scoreRepository->findByFiltersQueryBuilder($filters)->getQuery();

            $scores = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                50 // Number of scores per page
            );
            $searched = true;
        }

        // Get all zones for the add score dropdown
        $zones = $zoneRepository->findBy([], ['id' => 'ASC']);

        return $this->render('admin/score/list.html.twig', [
            'search_form' => $searchForm,
            'scores' => $scores,
            'searched' => $searched,
            'zones' => $zones,
        ]);
    }

    #[Route('/add/{id}', name: 'admin_score_add', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function add(
        Request $request,
        int $id,
        ZoneRepository $zoneRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $zone = $zoneRepository->find($id);

        if (!$zone) {
            $this->addFlash('error', 'Zone not found.');
            return $this->redirectToRoute('admin_score_list');
        }

        $score = new Score();
        $score->setZone($zone);

        $form = $this->createForm(ScoreType::class, $score, [
            'zone_id' => $zone->getId(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->addFlash('error', 'Form validation failed. Please check the fields.');
            } else {
                try {
                    $this->scoreService->addScore($score);
                    $this->addFlash('success', 'Score added successfully.');
                    return $this->redirectToRoute('admin_score_list');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error adding score: ' . $e->getMessage());
                }
            }
        }

        return $this->render('admin/score/form.html.twig', [
            'form' => $form,
            'score' => $score,
            'zone' => $zone,
            'is_edit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_score_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        Request $request,
        Score $score,
        EntityManagerInterface $entityManager
    ): Response {
        // Store old values for ranking recalculation
        $oldScoreValue = $score->getScore();
        $oldGlitch = $score->getGlitch();

        $form = $this->createForm(ScoreType::class, $score, [
            'zone_id' => $score->getZone()->getId(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->addFlash('error', 'Form validation failed. Please check the fields.');
            } else {
                try {
                    $this->scoreService->updateScore($score, $oldScoreValue, $oldGlitch);
                    $this->addFlash('success', 'Score updated successfully.');
                    return $this->redirectToRoute('admin_score_list');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error updating score: ' . $e->getMessage());
                }
            }
        }

        return $this->render('admin/score/form.html.twig', [
            'form' => $form,
            'score' => $score,
            'zone' => $score->getZone(),
            'is_edit' => true,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_score_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Score $score, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $score->getId(), (string) $request->request->get('_token'))) {
            try {
                $this->scoreService->deleteScore($score);
                $this->addFlash('success', 'Score deleted successfully.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error deleting score: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('admin_score_list');
    }
}
