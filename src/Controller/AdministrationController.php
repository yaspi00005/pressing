<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\JournalRepository;
use App\Repository\UserRepository;
use App\Service\Journalisation;
use App\Service\Paginateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/administration')]
class AdministrationController extends AbstractController
{
    #[Route('/utilisateurs', name: 'app_admin_users', methods: ['GET'])]
    public function users(UserRepository $users): Response
    {
        return $this->render('administration/users.html.twig', ['users' => $users->findBy([], ['nom' => 'ASC', 'prenom' => 'ASC'])]);
    }

    #[Route('/utilisateurs/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, Journalisation $journal): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles([$form->get('role')->getData()]);
            $user->setPassword($hasher->hashPassword($user, (string) $form->get('plainPassword')->getData()));
            $em->persist($user);
            $em->flush();
            $journal->noter('creation', 'Utilisateur créé : '.$user->getUsername().' ('.$user->getRoleLabel().')', 'user', $user->getId());
            $em->flush();
            $this->addFlash('success', 'Utilisateur « '.$user->getUsername().' » créé.');

            return $this->redirectToRoute('app_admin_users', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('administration/user_form.html.twig', ['form' => $form->createView(), 'titre' => 'Nouvel utilisateur', 'user' => $user], new Response(status: $form->isSubmitted() ? 422 : 200));
    }

    #[Route('/utilisateurs/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, User $user, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, Journalisation $journal): Response
    {
        $role = array_values(array_intersect(array_keys(User::ROLES), $user->getRoles()))[0] ?? 'ROLE_RECEPTION';
        $form = $this->createForm(UserType::class, $user, ['mot_de_passe_obligatoire' => false, 'role_actuel' => $role]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nouveauRole = $form->get('role')->getData();
            // On ne retire pas le dernier administrateur actif.
            if ('ROLE_ADMIN' !== $nouveauRole && \in_array('ROLE_ADMIN', $user->getRoles(), true) && $this->nombreAdminsActifs($em) <= 1) {
                $this->addFlash('danger', 'Impossible : il doit rester au moins un administrateur actif.');

                return $this->redirectToRoute('app_admin_user_edit', ['id' => $user->getId()]);
            }
            $user->setRoles([$nouveauRole]);
            if ($mdp = (string) $form->get('plainPassword')->getData()) {
                $user->setPassword($hasher->hashPassword($user, $mdp));
            }
            $journal->noter('modification', 'Utilisateur modifié : '.$user->getUsername().' ('.$user->getRoleLabel().')'.($mdp ? ', mot de passe changé' : ''), 'user', $user->getId());
            $em->flush();
            $this->addFlash('success', 'Utilisateur mis à jour.');

            return $this->redirectToRoute('app_admin_users', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('administration/user_form.html.twig', ['form' => $form->createView(), 'titre' => 'Modifier '.$user->getUsername(), 'user' => $user], new Response(status: $form->isSubmitted() ? 422 : 200));
    }

    #[Route('/utilisateurs/{id}/actif', name: 'app_admin_user_toggle', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggle(Request $request, User $user, EntityManagerInterface $em, Journalisation $journal): Response
    {
        if (!$this->isCsrfTokenValid('user'.$user->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        if ($user === $this->getUser()) {
            $this->addFlash('warning', 'Vous ne pouvez pas désactiver votre propre compte.');
        } elseif ($user->isActif() && \in_array('ROLE_ADMIN', $user->getRoles(), true) && $this->nombreAdminsActifs($em) <= 1) {
            $this->addFlash('danger', 'Impossible : il doit rester au moins un administrateur actif.');
        } else {
            $user->setActif(!$user->isActif());
            $journal->noter('modification', 'Compte '.($user->isActif() ? 'réactivé' : 'désactivé').' : '.$user->getUsername(), 'user', $user->getId());
            $em->flush();
            $this->addFlash('success', 'Compte '.($user->isActif() ? 'réactivé' : 'désactivé').'.');
        }

        return $this->redirectToRoute('app_admin_users', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/journal', name: 'app_admin_journal', methods: ['GET'])]
    public function journal(Request $request, JournalRepository $repo, UserRepository $users, Paginateur $paginateur): Response
    {
        $q = trim((string) $request->query->get('q'));
        $cible = (string) $request->query->get('cible');
        $userId = $request->query->getInt('user') ?: null;

        return $this->render('administration/journal.html.twig', [
            'pagination' => $paginateur->paginer($repo->filtre($q ?: null, $cible ?: null, $userId), $request, 50),
            'users' => $users->findBy([], ['nom' => 'ASC']),
            'filtres' => ['q' => $q, 'cible' => $cible, 'user' => $userId],
        ]);
    }

    private function nombreAdminsActifs(EntityManagerInterface $em): int
    {
        $n = 0;
        foreach ($em->getRepository(User::class)->findBy(['actif' => true]) as $u) {
            if (\in_array('ROLE_ADMIN', $u->getRoles(), true)) {
                ++$n;
            }
        }

        return $n;
    }
}
