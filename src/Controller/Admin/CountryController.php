<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Country;
use App\Form\CountryType;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/countries')]
#[IsGranted('ROLE_ADMIN')]
class CountryController extends AbstractController
{
    #[Route('', name: 'admin_country_list', methods: ['GET'])]
    public function list(
        Request $request,
        CountryRepository $countryRepository,
        PaginatorInterface $paginator
    ): Response {
        $query = $countryRepository->findAllOrderedByNameQueryBuilder()->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            30
        );

        return $this->render('admin/country/list.html.twig', [
            'countries' => $pagination,
        ]);
    }

    #[Route('/add', name: 'admin_country_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $country = new Country();
        $form = $this->createForm(CountryType::class, $country);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($country);
            $entityManager->flush();

            $this->addFlash('success', 'Country created successfully.');

            return $this->redirectToRoute('admin_country_list');
        }

        return $this->render('admin/country/form.html.twig', [
            'form' => $form,
            'country' => $country,
            'is_edit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_country_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Country $country,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(CountryType::class, $country);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Country updated successfully.');

            return $this->redirectToRoute('admin_country_list');
        }

        return $this->render('admin/country/form.html.twig', [
            'form' => $form,
            'country' => $country,
            'is_edit' => true,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_country_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Country $country,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $country->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($country);
            $entityManager->flush();

            $this->addFlash('success', 'Country deleted successfully.');
        }

        return $this->redirectToRoute('admin_country_list');
    }
}
