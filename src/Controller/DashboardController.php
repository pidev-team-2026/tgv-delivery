<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\User;
use App\Entity\Merchant;
use App\Entity\Courier;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

final class DashboardController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Get all users
        $users = $userRepository->findAll();
        
        // Calculate statistics
        $totalUsers = count($users);
        $totalClients = count(array_filter($users, fn($user) => $user->getRole() === 'client'));
        $totalMerchants = count(array_filter($users, fn($user) => $user->getRole() === 'merchant'));
        $totalCouriers = count(array_filter($users, fn($user) => $user->getRole() === 'courier'));
        
        // Get merchants and couriers with their data
        $merchants = $entityManager->getRepository(Merchant::class)->findAll();
        $couriers = $entityManager->getRepository(Courier::class)->findAll();
        
        // Calculate additional statistics
        $activeMerchants = count(array_filter($merchants, fn($merchant) => $merchant->getBusinessName() !== null));
        $activeCouriers = count(array_filter($couriers, fn($courier) => $courier->getLicenseNumber() !== null));
        
        // Prepare data for charts
        $roleDistribution = [
            'clients' => $totalClients,
            'merchants' => $totalMerchants,
            'couriers' => $totalCouriers
        ];
        
        // Recent users (last 5)
        $recentUsers = array_slice($users, -5);
        
        return $this->render('dashboard/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'totalClients' => $totalClients,
            'totalMerchants' => $totalMerchants,
            'totalCouriers' => $totalCouriers,
            'activeMerchants' => $activeMerchants,
            'activeCouriers' => $activeCouriers,
            'roleDistribution' => $roleDistribution,
            'recentUsers' => $recentUsers,
            'users' => $users
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(): Response
    {
        return $this->redirectToRoute('app_home');
    }
}
