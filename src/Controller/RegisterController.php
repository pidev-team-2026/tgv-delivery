<?php

namespace App\Controller;

use App\Form\Model\UserRegistrationData;
use App\Form\Registration\ClientRegistrationType;
use App\Form\Registration\MerchantRegistrationType;
use App\Form\Registration\CourierRegistrationType;
use App\Entity\User;
use App\Entity\Merchant;
use App\Entity\Courier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

final class RegisterController extends AbstractController
{
    #[Route('/register/{role}', name: 'app_register', defaults: ['role' => 'client'])]
    public function index(string $role, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
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
                    ->addError(new FormError('Les mots de passe ne correspondent pas'));
            } else {
                // Créer l'utilisateur
                $user = new User();
                $user->setFirstName($data->firstName);
                $user->setLastName($data->lastName);
                $user->setEmail($data->email);
                $user->setPhone($data->phone);
                $user->setRole($role);
                
                // Hasher le mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $data->password);
                $user->setPassword($hashedPassword);

                // Créer les entités spécifiques selon le rôle
                if ($role === 'merchant') {
                    $merchant = new Merchant();
                    $merchant->setCin($data->cin);
                    $merchant->setBusinessName($data->businessName);
                    $merchant->setBusinessType($data->businessType);
                    $merchant->setLocation($data->location);
                    $merchant->setUser($user);
                    $entityManager->persist($merchant);
                } elseif ($role === 'courier') {
                    $courier = new Courier();
                    $courier->setCin($data->cin);
                    $courier->setLicenseNumber($data->licenseNumber);
                    $courier->setVehicleType($data->vehicleType);
                    $courier->setUser($user);
                    $entityManager->persist($courier);
                }

                // Sauvegarder l'utilisateur
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Compte créé avec succès!');
                
                // Rediriger vers le login
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('register/register.html.twig', [
            'form' => $form->createView(),
            'role' => $role,
        ]);
    }
}
