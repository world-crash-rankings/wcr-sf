<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class UserController extends AbstractController
{
    #[Route('', name: 'admin_user_list', methods: ['GET'])]
    public function list(Request $request, UserRepository $userRepository, PaginatorInterface $paginator): Response
    {
        $query = $userRepository->findAllOrderedByUsernameQueryBuilder()->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/user/list.html.twig', [
            'users' => $pagination,
        ]);
    }

    #[Route('/add', name: 'admin_user_add', methods: ['GET', 'POST'])]
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_edit' => false]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash password
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Set roles based on selected role
            $selectedRole = $form->get('userRole')->getData();
            if ($selectedRole === 'ROLE_SUPER_ADMIN') {
                $user->setRoles(['ROLE_SUPER_ADMIN']);
            } elseif ($selectedRole === 'ROLE_ADMIN') {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles([]);
            }

            // Set timestamps
            $user->setCreatedAt(new \DateTime());

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User created successfully.');

            return $this->redirectToRoute('admin_user_list');
        }

        return $this->render('admin/user/form.html.twig', [
            'form' => $form,
            'user' => $user,
            'is_edit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update password only if provided
            $plainPassword = $form->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // Update roles based on selected role
            $selectedRole = $form->get('userRole')->getData();
            if ($selectedRole === 'ROLE_SUPER_ADMIN') {
                $user->setRoles(['ROLE_SUPER_ADMIN']);
            } elseif ($selectedRole === 'ROLE_ADMIN') {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles([]);
            }

            // Update timestamp
            $user->setUpdatedAt(new \DateTime());

            $entityManager->flush();

            $this->addFlash('success', 'User updated successfully.');

            return $this->redirectToRoute('admin_user_list');
        }

        return $this->render('admin/user/form.html.twig', [
            'form' => $form,
            'user' => $user,
            'is_edit' => true,
        ]);
    }
}
