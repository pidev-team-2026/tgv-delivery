<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Form\RendezVousDemandeType;
use App\Repository\CommercantRepository;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur Front Office (pages publiques).
 */
#[Route(name: 'app_front_')]
final class FrontController extends AbstractController
{
    #[Route('/', name: 'accueil', methods: ['GET'])]
    public function accueil(): Response
    {
        return $this->render('front/accueil.html.twig');
    }

    /**
     * Liste publique des commerçants (Front Office).
     */
    #[Route('/commercants', name: 'commercants_list', methods: ['GET'])]
    public function commercantsList(CommercantRepository $commercantRepository, Request $request): Response
    {
        $search = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'nom');
        $order = $request->query->get('order', 'asc');

        $commercants = $commercantRepository->findForFront($search, $sort, $order);

        return $this->render('front/commercants_list.html.twig', [
            'commercants' => $commercants,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
        ]);
    }

    /**
     * Demander un rendez-vous (utilisateur) — créé avec état En attente ; le commerçant accepte ou refuse ensuite.
     */
    #[Route('/rendez-vous/demander/{commercantId}', name: 'rendez_vous_demander', requirements: ['commercantId' => '\\d+'], defaults: ['commercantId' => null], methods: ['GET', 'POST'])]
    public function demanderRendezVous(Request $request, EntityManagerInterface $entityManager, CommercantRepository $commercantRepository, ?int $commercantId = null): Response
    {
        $rendezVous = new RendezVous();
        $rendezVous->setEtat(RendezVous::ETAT_EN_ATTENTE);

        $commercantFixed = false;
        if ($commercantId !== null) {
            $commercant = $commercantRepository->find($commercantId);
            if ($commercant !== null) {
                $rendezVous->setCommercant($commercant);
                $commercantFixed = true;
            }
        }

        $form = $this->createForm(RendezVousDemandeType::class, $rendezVous, [
            'commercant_fixed' => $commercantFixed,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rendezVous);
            $entityManager->flush();
            $this->addFlash('success', 'Votre demande de rendez-vous a été envoyée. Le commerçant vous répondra (acceptation ou refus).');

            return $this->redirectToRoute('app_front_commercants_list');
        }

        return $this->render('front/rendez_vous_demander.html.twig', [
            'rendez_vous' => $rendezVous,
            'form' => $form,
        ]);
    }

    /**
     * Mes rendez-vous (utilisateur connecté).
     */
    #[Route('/mes-rendez-vous', name: 'mes_rendez_vous', methods: ['GET'])]
    public function mesRendezVous(RendezVousRepository $rendezVousRepository): Response
    {
        // Pour l'instant, nous utilisons une session ou un email en dur
        // Dans un vrai projet, vous utiliseriez l'utilisateur connecté
        $emailDemandeur = $_SESSION['user_email'] ?? 'user@example.com';
        
        $rendezVous = $rendezVousRepository->findByEmailDemandeur($emailDemandeur);

        return $this->render('front/mes_rendez_vous.html.twig', [
            'rendez_vous' => $rendezVous,
            'email_demandeur' => $emailDemandeur,
        ]);
    }
}
