<?php

namespace App\Controller\Back;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReclamationType;
use App\Form\ReponseType;
use App\Repository\ReclamationRepository;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class ReclamationController extends AbstractController
{
    #[Route('', name: 'app_back_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->redirectToRoute('app_back_reclamation_index');
    }

    #[Route('/reclamations', name: 'app_back_reclamation_index', methods: ['GET'])]
    public function index(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        $search = $request->query->get('search', '');
        $sortBy = $request->query->get('sort_by', 'createdAt');
        $sortOrder = $request->query->get('sort_order', 'DESC');

        $reclamations = $reclamationRepository->findAllWithSearchAndSort($search ?: null, $sortBy, $sortOrder);

        return $this->render('back/reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'search' => $search,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ]);
    }

    #[Route('/reclamations/{id}', name: 'app_back_reclamation_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('back/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/reclamations/{id}/edit', name: 'app_back_reclamation_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation, ['include_status' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Réclamation mise à jour avec succès.');
            return $this->redirectToRoute('app_back_reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('back/reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/reclamations/{id}/delete', name: 'app_back_reclamation_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->getString('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
            $this->addFlash('success', 'Réclamation supprimée avec succès.');
        }

        return $this->redirectToRoute('app_back_reclamation_index');
    }

    #[Route('/reclamations/{id}/reponses/new', name: 'app_back_reponse_new', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function reponseNew(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $reponse = new Reponse();
        $reponse->setReclamation($reclamation);
        $reponse->setAuthor('Admin');

        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reponse);
            $entityManager->flush();
            $this->addFlash('success', 'Réponse ajoutée avec succès.');
            return $this->redirectToRoute('app_back_reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('back/reponse/new.html.twig', [
            'reclamation' => $reclamation,
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/reclamations/{reclamationId}/reponses/{id}/edit', name: 'app_back_reponse_edit', requirements: ['reclamationId' => '\d+', 'id' => '\d+'], methods: ['GET', 'POST'])]
    public function reponseEdit(Request $request, int $reclamationId, Reponse $reponse, EntityManagerInterface $entityManager, ReponseRepository $reponseRepository): Response
    {
        if ($reponse->getReclamation()->getId() !== $reclamationId) {
            throw $this->createNotFoundException('Réponse non trouvée.');
        }

        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Réponse modifiée avec succès.');
            return $this->redirectToRoute('app_back_reclamation_show', ['id' => $reclamationId]);
        }

        return $this->render('back/reponse/edit.html.twig', [
            'reclamation' => $reponse->getReclamation(),
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/reclamations/{reclamationId}/reponses/{id}/delete', name: 'app_back_reponse_delete', requirements: ['reclamationId' => '\d+', 'id' => '\d+'], methods: ['POST'])]
    public function reponseDelete(Request $request, int $reclamationId, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        if ($reponse->getReclamation()->getId() !== $reclamationId) {
            throw $this->createNotFoundException('Réponse non trouvée.');
        }

        if ($this->isCsrfTokenValid('delete_reponse' . $reponse->getId(), $request->request->getString('_token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();
            $this->addFlash('success', 'Réponse supprimée avec succès.');
        }

        return $this->redirectToRoute('app_back_reclamation_show', ['id' => $reclamationId]);
    }
}
