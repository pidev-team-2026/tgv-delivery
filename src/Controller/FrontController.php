<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\User;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
class FrontController extends AbstractController
{
    #[Route('', name: 'app_front_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    #[Route('select-user', name: 'app_front_select_user', methods: ['GET', 'POST'])]
    public function selectUser(Request $request, UserRepository $userRepository): Response
    {
        if ($request->isMethod('POST')) {
            $userId = $request->request->getInt('user_id');
            if ($userId) {
                $user = $userRepository->find($userId);
                if ($user) {
                    $request->getSession()->set('front_user_id', $user->getId());
                    $this->addFlash('success', 'Bienvenue ' . $user->getName() . ' !');
                    return $this->redirectToRoute('app_front_reclamation_index');
                }
            }
        }

        $users = $userRepository->findBy([], ['name' => 'ASC']);

        return $this->render('front/select_user.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('logout', name: 'app_front_logout', methods: ['GET'])]
    public function logout(Request $request): Response
    {
        $request->getSession()->remove('front_user_id');
        $this->addFlash('success', 'Vous avez été déconnecté.');
        return $this->redirectToRoute('app_front_home');
    }

    #[Route('reclamations', name: 'app_front_reclamation_index', methods: ['GET'])]
    public function reclamationIndex(Request $request, ReclamationRepository $reclamationRepository, UserRepository $userRepository): Response
    {
        $userId = $request->getSession()->get('front_user_id');
        if (!$userId) {
            $this->addFlash('warning', 'Veuillez sélectionner votre compte pour accéder à vos réclamations.');
            return $this->redirectToRoute('app_front_select_user');
        }

        $user = $userRepository->find($userId);
        if (!$user) {
            $request->getSession()->remove('front_user_id');
            return $this->redirectToRoute('app_front_select_user');
        }

        $search = $request->query->get('q', '');
        $reclamations = $reclamationRepository->findByUserAndSearch($user, $search ?: null);

        return $this->render('front/reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'user' => $user,
            'search' => $search,
        ]);
    }

    #[Route('reclamations/new', name: 'app_front_reclamation_new', methods: ['GET', 'POST'])]
    public function reclamationNew(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $userId = $request->getSession()->get('front_user_id');
        if (!$userId) {
            $this->addFlash('warning', 'Veuillez sélectionner votre compte pour créer une réclamation.');
            return $this->redirectToRoute('app_front_select_user');
        }

        $user = $userRepository->find($userId);
        if (!$user) {
            $request->getSession()->remove('front_user_id');
            return $this->redirectToRoute('app_front_select_user');
        }

        $reclamation = new Reclamation();
        $reclamation->setUser($user);

        $form = $this->createForm(ReclamationType::class, $reclamation, ['include_user' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mode = $request->request->get('reclamation_mode', 'text');
            $voicePayload = $request->request->get('voice_payload');

            // En mode vocal, si aucun texte n'a été saisi, on met un message par défaut
            if ($mode === 'voice' && (!$reclamation->getMessage() || trim((string) $reclamation->getMessage()) === '')) {
                $reclamation->setMessage('Message vocal enregistré (transcription et analyse IA à traiter côté serveur).');
            }

            $entityManager->persist($reclamation);
            $entityManager->flush();

            // Sauvegarde du fichier audio sur le disque (sans toucher à la base de données)
            if ($mode === 'voice' && \is_string($voicePayload) && str_starts_with($voicePayload, 'data:audio')) {
                $parts = explode(',', $voicePayload, 2);
                if (\count($parts) === 2) {
                    $meta = $parts[0];
                    $data = $parts[1];
                    $extension = 'webm';
                    if (str_contains($meta, 'audio/ogg')) {
                        $extension = 'ogg';
                    } elseif (str_contains($meta, 'audio/mp3') || str_contains($meta, 'audio/mpeg')) {
                        $extension = 'mp3';
                    }

                    $binary = base64_decode($data);
                    if ($binary !== false) {
                        $projectDir = $this->getParameter('kernel.project_dir');
                        $uploadDir = $projectDir . '/public/uploads/reclamations';
                        if (!is_dir($uploadDir)) {
                            @mkdir($uploadDir, 0775, true);
                        }
                        $filePath = $uploadDir . '/reclamation_' . $reclamation->getId() . '.' . $extension;
                        @file_put_contents($filePath, $binary);
                    }
                }
            }

            $this->addFlash('success', 'Votre réclamation a été créée avec succès.');
            return $this->redirectToRoute('app_front_reclamation_index');
        }

        return $this->render('front/reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('reclamations/{id}', name: 'app_front_reclamation_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function reclamationShow(Reclamation $reclamation, Request $request, UserRepository $userRepository): Response
    {
        $userId = $request->getSession()->get('front_user_id');
        if (!$userId || $reclamation->getUser()->getId() !== $userId) {
            $this->addFlash('error', 'Accès non autorisé à cette réclamation.');
            return $this->redirectToRoute('app_front_reclamation_index');
        }

        return $this->render('front/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('reclamations/{id}/edit', name: 'app_front_reclamation_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function reclamationEdit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $userId = $request->getSession()->get('front_user_id');
        if (!$userId || $reclamation->getUser()->getId() !== $userId) {
            $this->addFlash('error', 'Accès non autorisé à cette réclamation.');
            return $this->redirectToRoute('app_front_reclamation_index');
        }

        $form = $this->createForm(ReclamationType::class, $reclamation, ['include_user' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Votre réclamation a été modifiée avec succès.');
            return $this->redirectToRoute('app_front_reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('front/reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('reclamations/{id}/delete', name: 'app_front_reclamation_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function reclamationDelete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $userId = $request->getSession()->get('front_user_id');
        if (!$userId || $reclamation->getUser()->getId() !== $userId) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('app_front_reclamation_index');
        }

        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->getString('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
            $this->addFlash('success', 'Réclamation supprimée avec succès.');
        }

        return $this->redirectToRoute('app_front_reclamation_index');
    }
}
