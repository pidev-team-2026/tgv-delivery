<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Commande;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
{
    #[Route('/client', name: 'client_')]
    public function index(ProduitRepository $produitRepository, CommandeRepository $commandeRepository): Response
    {
    
        $totalProduits = $produitRepository->count([]);
        $totalCommandes = $commandeRepository->count([]);
        $totalLivraisons = $commandeRepository->findBy(['statut' => 'livrée']);
        $totalEnCours = $commandeRepository->findBy(['statut' => ['en attente', 'confirmée', 'expédiée']]);
        
        // Récupérer les 3 derniers produits
        $derniersProduits = $produitRepository->findBy([], ['dateCreation' => 'DESC'], 3);
        
        return $this->render('client/index.html.twig', [
            'totalProduits' => $totalProduits,
            'totalCommandes' => $totalCommandes,
            'totalLivraisons' => count($totalLivraisons),
            'totalEnCours' => count($totalEnCours),
            'derniersProduits' => $derniersProduits,
        ]);
    }

    #[Route('/client/produits', name: 'client_produits')]
    public function produits(ProduitRepository $produitRepository): Response
    {
        // Récupérer tous les produits actifs
        $produits = $produitRepository->findBy(['statut' => 'actif']);
        
        return $this->render('client/produits/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/client/commandes', name: 'client_commandes')]
    public function commandes(CommandeRepository $commandeRepository): Response
    {
        // Récupérer toutes les commandes
        $commandes = $commandeRepository->findBy([], ['dateCreation' => 'DESC']);
        
        return $this->render('client/commandes/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/client/produit/{id}', name: 'client_produit_show')]
    public function produitShow(Produit $produit): Response
    {
        return $this->render('client/produits/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/client/commande/{id}', name: 'client_commande_show')]
    public function commandeShow(Commande $commande): Response
    {
        return $this->render('client/commandes/show.html.twig', [
            'commande' => $commande,
        ]);
    }
}
