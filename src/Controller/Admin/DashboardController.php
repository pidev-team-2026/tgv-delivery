<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        // Vous pouvez récupérer les vraies données depuis la base de données
        // Pour l'instant, on utilise des données d'exemple
        
        return $this->render('admin/dashboard/dashboard_index.html.twig', [
            'totalCommandes' => 156,
            'totalProduits' => 89,
            'totalLivraisons' => 42,
            'totalRevenus' => 45678,
            'commandesAujourdhui' => 12,
            'revenusAujourdhui' => 2450,
            'livraisonsAujourdhui' => 8,
            'nouveauxClients' => 3,
            'recentCommandes' => [], // Vous pouvez ajouter un repository pour récupérer les vraies commandes
        ]);
    }
}
