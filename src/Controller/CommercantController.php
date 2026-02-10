<?php

namespace App\Controller;

use App\Entity\Commercant;
use App\Form\CommercantType;
use App\Repository\CommercantRepository;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commercant')]
final class CommercantController extends AbstractController
{
    /**
     * Tableau de bord Back Office (accueil admin).
     */
    #[Route('/dashboard', name: 'app_back_dashboard', methods: ['GET'])]
    public function dashboard(CommercantRepository $commercantRepository, RendezVousRepository $rendezVousRepository): Response
    {
        $nbCommercants = $commercantRepository->count([]);
        $nbRdv = $rendezVousRepository->count([]);
        $nbEnAttente = $rendezVousRepository->count(['etat' => 'EN_ATTENTE']);
        $statsParVille = $commercantRepository->countByVille();

        return $this->render('admin/dashboard.html.twig', [
            'nb_commercants' => $nbCommercants,
            'nb_rdv' => $nbRdv,
            'nb_en_attente' => $nbEnAttente,
            'stats_par_ville' => $statsParVille,
        ]);
    }

    #[Route(name: 'app_commercant_index', methods: ['GET'])]
    public function index(CommercantRepository $commercantRepository, Request $request): Response
    {
        $search = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'nom');
        $order = $request->query->get('order', 'asc');

        return $this->render('commercant/index.html.twig', [
            'commercants' => $commercantRepository->findWithSearchAndSort($search, $sort, $order),
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
        ]);
    }

    #[Route('/new', name: 'app_commercant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commercant = new Commercant();
        $form = $this->createForm(CommercantType::class, $commercant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commercant);
            $entityManager->flush();

            return $this->redirectToRoute('app_commercant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commercant/new.html.twig', [
            'commercant' => $commercant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commercant_show', methods: ['GET'])]
    public function show(Commercant $commercant): Response
    {
        return $this->render('commercant/show.html.twig', [
            'commercant' => $commercant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commercant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commercant $commercant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommercantType::class, $commercant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commercant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commercant/edit.html.twig', [
            'commercant' => $commercant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commercant_delete', methods: ['POST'])]
    public function delete(Request $request, Commercant $commercant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commercant->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($commercant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commercant_index', [], Response::HTTP_SEE_OTHER);
    }
}
