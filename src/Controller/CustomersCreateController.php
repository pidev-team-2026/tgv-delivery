<?php

namespace App\Controller;

use App\Form\Model\UserRegistrationData;
use App\Form\Registration\ClientRegistrationType;
use App\Form\Registration\MerchantRegistrationType;
use App\Form\Registration\CourierRegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormError;

final class CustomersCreateController extends AbstractController
{
    #[Route('/create/{role}', name: 'app_customers_create', defaults: ['role' => 'client'])]
    public function index(string $role, Request $request): Response
    {
        $data = new UserRegistrationData();

        switch ($role) {
            case 'merchant':
                $form = $this->createForm(MerchantRegistrationType::class, $data);
                break;

            case 'courier':
                $form = $this->createForm(CourierRegistrationType::class, $data);
                break;

            default:
                $role = 'client';
                $form = $this->createForm(ClientRegistrationType::class, $data);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($data->password !== $data->confirmPassword) {
                $form->get('confirmPassword')
                    ->addError(new FormError('Passwords do not match'));
            } else {
                $this->addFlash('success', 'Account created successfully!');
            }
        }

        return $this->render('customers_create/customers_create.html.twig', [
            'form' => $form->createView(),
            'role' => $role,
        ]);
    }
}
