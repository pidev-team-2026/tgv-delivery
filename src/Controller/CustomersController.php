<?php

namespace App\Controller;
use App\Repository\UserRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CustomersController extends AbstractController
{
    #[Route('/customers', name: 'app_customers')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('customers/customers.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
}
