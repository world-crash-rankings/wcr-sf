<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Zone;
use App\Form\ZoneType;
use App\Repository\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/zones')]
#[IsGranted('ROLE_ADMIN')]
class ZoneController extends AbstractController
{
    #[Route('', name: 'admin_zone_list', methods: ['GET'])]
    public function list(Request $request, ZoneRepository $zoneRepository, PaginatorInterface $paginator): Response
    {
        $query = $zoneRepository->findAllOrderedByIdQueryBuilder()->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            30
        );

        return $this->render('admin/zone/list.html.twig', [
            'zones' => $pagination,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_zone_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        int $id,
        ZoneRepository $zoneRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $zone = $zoneRepository->findWithStars($id);

        if (!$zone) {
            throw $this->createNotFoundException('Zone not found');
        }

        $form = $this->createForm(ZoneType::class, $zone);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Zone updated successfully.');

            return $this->redirectToRoute('admin_zone_list');
        }

        return $this->render('admin/zone/form.html.twig', [
            'form' => $form,
            'zone' => $zone,
        ]);
    }
}
