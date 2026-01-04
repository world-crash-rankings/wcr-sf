<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\News;
use App\Form\NewsType;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/news')]
#[IsGranted('ROLE_ADMIN')]
class NewsController extends AbstractController
{
    #[Route('', name: 'admin_news_list', methods: ['GET'])]
    public function list(Request $request, NewsRepository $newsRepository, PaginatorInterface $paginator): Response
    {
        $query = $newsRepository->findAllOrderedByDateQueryBuilder()->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/news/list.html.twig', [
            'news' => $pagination,
        ]);
    }

    #[Route('/add', name: 'admin_news_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $news = new News();
        $form = $this->createForm(NewsType::class, $news);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($news);
            $entityManager->flush();

            $this->addFlash('success', 'News created successfully.');

            return $this->redirectToRoute('admin_news_list');
        }

        return $this->render('admin/news/form.html.twig', [
            'form' => $form,
            'news' => $news,
            'is_edit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_news_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, News $news, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NewsType::class, $news);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'News updated successfully.');

            return $this->redirectToRoute('admin_news_list');
        }

        return $this->render('admin/news/form.html.twig', [
            'form' => $form,
            'news' => $news,
            'is_edit' => true,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_news_delete', methods: ['POST'])]
    public function delete(Request $request, News $news, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $news->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($news);
            $entityManager->flush();

            $this->addFlash('success', 'News deleted successfully.');
        }

        return $this->redirectToRoute('admin_news_list');
    }
}
