<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/rendez/vous')]
final class RendezVousController extends AbstractController
{
    #[Route(name: 'app_rendez_vous_index', methods: ['GET'])]
    public function index(RendezVousRepository $rendezVousRepository, Request $request): Response
    {
        $search = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'dateRdv');
        $order = $request->query->get('order', 'desc');

        return $this->render('rendez_vous/index.html.twig', [
            'rendez_vouses' => $rendezVousRepository->findWithSearchAndSort($search, $sort, $order),
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
        ]);
    }

    #[Route('/new', name: 'app_rendez_vous_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rendezVou = new RendezVous();
        $form = $this->createForm(RendezVousType::class, $rendezVou);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rendezVou);
            $entityManager->flush();

            return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rendez_vous/new.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_rendez_vous_show', methods: ['GET'])]
    public function show(RendezVous $rendezVou): Response
    {
        return $this->render('rendez_vous/show.html.twig', [
            'rendez_vou' => $rendezVou,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rendez_vous_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RendezVousType::class, $rendezVou);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rendez_vous/edit.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/accepter', name: 'app_rendez_vous_accepter', methods: ['POST'])]
    public function accepter(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager, MailerInterface $mailer, Environment $twig): Response
    {
        if ($this->isCsrfTokenValid('accepter' . $rendezVou->getId(), $request->request->get('_token'))) {
            $rendezVou->setEtat(RendezVous::ETAT_CONFIRME);
            $entityManager->flush();
            $this->envoyerNotificationRdv($rendezVou, 'validation', $mailer, $twig);
            $this->addFlash('success', 'Rendez-vous accepté. Un email a été envoyé au demandeur.');
        }

        return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/refuser', name: 'app_rendez_vous_refuser', methods: ['POST'])]
    public function refuser(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager, MailerInterface $mailer, Environment $twig): Response
    {
        if ($this->isCsrfTokenValid('refuser' . $rendezVou->getId(), $request->request->get('_token'))) {
            $rendezVou->setEtat(RendezVous::ETAT_ANNULE);
            $entityManager->flush();
            $this->envoyerNotificationRdv($rendezVou, 'refus', $mailer, $twig);
            $this->addFlash('success', 'Rendez-vous refusé. Un email a été envoyé au demandeur.');
        }

        return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
    }

    private function envoyerNotificationRdv(RendezVous $rendezVou, string $statut, MailerInterface $mailer, Environment $twig): void
    {
        $emailDestinataire = $rendezVou->getEmailDemandeur();
        if ($emailDestinataire === null || $emailDestinataire === '') {
            return;
        }

        try {
            $html = $twig->render('email/rendez_vous_notification.html.twig', [
                'rendezVous' => $rendezVou,
                'statut' => $statut,
            ]);

            $from = $_ENV['MAILER_FROM'] ?? 'noreply@gestion-service.local';
            $email = (new Email())
                ->from($from)
                ->to($emailDestinataire)
                ->subject($statut === 'validation'
                    ? 'Votre rendez-vous a été confirmé'
                    : 'Votre demande de rendez-vous a été refusée')
                ->html($html);

            $mailer->send($email);
        } catch (\Throwable $e) {
            $this->addFlash('warning', 'Email non envoyé : ' . $e->getMessage());
        }
    }

    #[Route('/{id}', name: 'app_rendez_vous_delete', methods: ['POST'])]
    public function delete(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rendezVou->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($rendezVou);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_rendez_vous_index', [], Response::HTTP_SEE_OTHER);
    }
}
