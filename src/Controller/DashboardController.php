<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(CommandeRepository $commandeRepository, ProduitRepository $produitRepository): Response
    {
        $totalCommandes = $commandeRepository->count([]);
        $totalProduits = $produitRepository->count([]);
        $totalLivraisons = $commandeRepository->countEnLivraisonOuLivrees();
        $totalRevenus = $commandeRepository->getRevenusMois();
        $commandesAujourdhui = $commandeRepository->countAujourdhui();
        $revenusAujourdhui = $commandeRepository->getRevenusAujourdhui();
        $livraisonsAujourdhui = $commandeRepository->countLivraisonsAujourdhui();
        $recentCommandes = $commandeRepository->findRecent(10);

        return $this->render('admin/dashboard/admin_dashboard.html.twig', [
            'totalCommandes' => $totalCommandes,
            'totalProduits' => $totalProduits,
            'totalLivraisons' => $totalLivraisons,
            'totalRevenus' => $totalRevenus,
            'commandesAujourdhui' => $commandesAujourdhui,
            'revenusAujourdhui' => $revenusAujourdhui,
            'livraisonsAujourdhui' => $livraisonsAujourdhui,
            'nouveauxClients' => $commandesAujourdhui,
            'recentCommandes' => $recentCommandes,
        ]);
    }
}
