<?php

namespace App\Controller;

use App\Form\Model\UserRegistrationData;
use App\Form\Registration\ClientRegistrationType;
use App\Form\Registration\MerchantRegistrationType;
use App\Form\Registration\CourierRegistrationType;
use App\Entity\User;
use App\Entity\Merchant;
use App\Entity\Courier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegistrationController extends AbstractController
{
    #[Route('/registration/{role}', name: 'app_registration')]
    public function register(
        string $role,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        // ✅ Normalize role
        if (!in_array($role, ['client', 'merchant', 'courier'])) {
            $role = 'client';
        }

        // 1️⃣ DTO
        $data = new UserRegistrationData();

        // 2️⃣ Select form
        switch ($role) {
            case 'merchant':
                $form = $this->createForm(MerchantRegistrationType::class, $data);
                break;

            case 'courier':
                $form = $this->createForm(CourierRegistrationType::class, $data);
                break;

            default:
                $form = $this->createForm(ClientRegistrationType::class, $data);
        }

        // 3️⃣ Handle request
        $form->handleRequest($request);

        // 4️⃣ Validate & Save
        if ($form->isSubmitted() && $form->isValid()) {

            // 🔐 Password confirmation
            if ($data->password !== $data->confirmPassword) {
                $form->get('confirmPassword')
                    ->addError(new FormError('Passwords do not match'));
            } else {

                // ======================
                // USER
                // ======================
                $user = new User();
                $user->setFirstName($data->firstName);
                $user->setLastName($data->lastName);
                $user->setEmail($data->email);
                $user->setPhone($data->phone);
                $user->setRole($role); // ✅ CORRECT (NOT setRoles)

                // 🔐 Hash password
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $data->password
                );
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);

                // ======================
                // MERCHANT
                // ======================
                if ($role === 'merchant') {
                    $merchant = new Merchant();
                    $merchant->setUser($user);
                    $merchant->setCin($data->cin);
                    $merchant->setBusinessName($data->businessName);
                    $merchant->setBusinessType($data->businessType);
                    $merchant->setLocation($data->location);

                    $entityManager->persist($merchant);
                }

                // ======================
                // COURIER
                // ======================
                if ($role === 'courier') {
                    $courier = new Courier();
                    $courier->setUser($user);
                    $courier->setCin($data->cin);
                    $courier->setLicenseNumber($data->licenseNumber);
                    $courier->setVehicleType($data->vehicleType);

                    $entityManager->persist($courier);
                }

                // ======================
                // SAVE
                // ======================
                $entityManager->flush();

                $this->addFlash('success', 'Account created successfully');

                return $this->redirectToRoute('app_login');
            }
        }

        // 5️⃣ Render
        return $this->render('customers_create/customers_create.html.twig', [
            'form' => $form->createView(),
            'role' => $role,
        ]);
    }
}
