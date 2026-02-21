<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Commande;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
{
    // ========================================
    // PAGE D'ACCUEIL
    // ========================================
    #[Route('/client', name: 'client_')]
    public function index(ProduitRepository $produitRepository, CommandeRepository $commandeRepository): Response
    {
        $totalProduits = $produitRepository->count([]);
        $totalCommandes = $commandeRepository->count([]);
        $totalLivraisons = $commandeRepository->findBy(['statut' => 'livrée']);
        $totalEnCours = $commandeRepository->findBy(['statut' => ['en attente', 'confirmée', 'expédiée']]);
        
        $derniersProduits = $produitRepository->findBy([], ['dateCreation' => 'DESC'], 3);
        
        return $this->render('client/index.html.twig', [
            'totalProduits' => $totalProduits,
            'totalCommandes' => $totalCommandes,
            'totalLivraisons' => count($totalLivraisons),
            'totalEnCours' => count($totalEnCours),
            'derniersProduits' => $derniersProduits,
        ]);
    }

    // ========================================
    // SHOP - PRODUITS
    // ========================================
    #[Route('/client/produits', name: 'client_produits')]
    public function produits(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findBy([
            'statut' => ['disponible', 'bientot_disponible', 'rupture'],
        ]);
        
        return $this->render('client/produits/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/client/produit/{id}', name: 'client_produit_show')]
    public function produitShow(Produit $produit): Response
    {
        return $this->render('client/produits/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    // ========================================
    // SHOP - COMMANDES
    // ========================================
    #[Route('/client/commandes', name: 'client_commandes')]
    public function commandes(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findBy([], ['dateCreation' => 'DESC']);
        
        return $this->render('client/commandes/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    /** Détails commande côté client */
    #[Route('/client/commande/{id}', name: 'client_commande_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function commandeShow(Commande $commande): Response
    {
        return $this->render('client/commandes/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    /** Annuler une commande (seulement si en_attente) */
    #[Route('/client/commande/{id}/annuler', name: 'client_commande_annuler', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function commandeAnnuler(Commande $commande, EntityManagerInterface $em, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('annuler' . $commande->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('client_commandes');
        }

        if ($commande->getStatut() === 'en_attente') {
            $commande->setStatut('annulee');
            $em->flush();
            $this->addFlash('success', '✅ Commande ' . $commande->getReference() . ' annulée avec succès.');
        } else {
            $this->addFlash('error', '❌ Cette commande ne peut plus être annulée (statut : ' . $commande->getStatutLibelle() . ').');
        }

        return $this->redirectToRoute('client_commandes');
    }

    // ========================================
    // SERVICES
    // ========================================
    #[Route('/client/services', name: 'client_services')]
    public function services(): Response
    {
        return $this->render('client/services/index.html.twig');
    }

    // ========================================
    // SUPPORT - RÉCLAMATIONS
    // ========================================
    #[Route('/client/reclamations', name: 'client_reclamations')]
    public function reclamations(): Response
    {
        return $this->render('client/support/reclamations.html.twig');
    }

    // ========================================
    // SUPPORT - CONTACT
    // ========================================
    #[Route('/client/support', name: 'client_support')]
    public function support(): Response
    {
        return $this->render('client/support/contact.html.twig');
    }

    // ========================================
    // PARTENAIRES
    // ========================================
    #[Route('/client/partenaires', name: 'client_partenaires')]
    public function partenaires(): Response
    {
        return $this->render('client/partenaires/index.html.twig');
    }
}
