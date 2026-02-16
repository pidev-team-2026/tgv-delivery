<?php

namespace App\Controller;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Entity\Merchant;
use App\Entity\Courier;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

final class CustomersController extends AbstractController
{
    #[Route('/customers', name: 'app_customers')]
    public function index(UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $users = $userRepository->findAll();
        $userData = [];

        foreach ($users as $user) {
            $userData[$user->getId()] = [
                'user' => $user,
                'merchant' => null,
                'courier' => null
            ];

            // Load merchant data if exists
            if ($user->getRole() === 'merchant') {
                $merchant = $entityManager->getRepository(Merchant::class)->findOneBy(['user' => $user]);
                $userData[$user->getId()]['merchant'] = $merchant;
            }

            // Load courier data if exists
            if ($user->getRole() === 'courier') {
                $courier = $entityManager->getRepository(Courier::class)->findOneBy(['user' => $user]);
                $userData[$user->getId()]['courier'] = $courier;
            }
        }

        return $this->render('customers/customers.html.twig', [
            'users' => $users,
            'userData' => $userData,
        ]);
    }

    #[Route('/customers/create', name: 'app_customers_create')]
    public function create(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            // Validate unique fields
            $email = $request->request->get('email');
            $phone = $request->request->get('phone');
            $cin = $request->request->get('cin');
            $role = $request->request->get('role');

            // Check if email already exists
            $existingUserByEmail = $userRepository->findOneBy(['email' => $email]);
            if ($existingUserByEmail) {
                $this->addFlash('error', 'Cet email est déjà utilisé par un autre utilisateur.');
                return $this->redirectToRoute('app_customers_create');
            }

            // Check if phone already exists
            $existingUserByPhone = $userRepository->findOneBy(['phone' => $phone]);
            if ($existingUserByPhone) {
                $this->addFlash('error', 'Ce numéro de téléphone est déjà utilisé par un autre utilisateur.');
                return $this->redirectToRoute('app_customers_create');
            }

            // Check if CIN already exists (for merchant and courier roles)
            if ($role === 'merchant' || $role === 'courier') {
                if ($role === 'merchant') {
                    $existingMerchant = $entityManager->getRepository(Merchant::class)->findOneBy(['cin' => $cin]);
                    if ($existingMerchant) {
                        $this->addFlash('error', 'Ce CIN est déjà utilisé par un autre commerçant.');
                        return $this->redirectToRoute('app_customers_create');
                    }
                } elseif ($role === 'courier') {
                    $existingCourier = $entityManager->getRepository(Courier::class)->findOneBy(['cin' => $cin]);
                    if ($existingCourier) {
                        $this->addFlash('error', 'Ce CIN est déjà utilisé par un autre livreur.');
                        return $this->redirectToRoute('app_customers_create');
                    }
                }
            }

            // Create new user
            $user = new User();
            $user->setFirstName($request->request->get('firstName'));
            $user->setLastName($request->request->get('lastName'));
            $user->setEmail($email);
            $user->setPhone($phone);
            $user->setRole($role);

            $entityManager->persist($user);

            // Create merchant or courier if applicable
            if ($role === 'merchant') {
                $merchant = new Merchant();
                $merchant->setUser($user);
                $merchant->setCin($cin);
                $merchant->setBusinessName($request->request->get('businessName'));
                $merchant->setBusinessType($request->request->get('businessType'));
                $merchant->setLocation($request->request->get('location'));
                $entityManager->persist($merchant);
            } elseif ($role === 'courier') {
                $courier = new Courier();
                $courier->setUser($user);
                $courier->setCin($cin);
                $courier->setLicenseNumber($request->request->get('licenseNumber'));
                $courier->setVehicleType($request->request->get('vehicleType'));
                $entityManager->persist($courier);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');
            return $this->redirectToRoute('app_customers');
        }

        return $this->render('customers/create.html.twig');
    }

    #[Route('/customers/update/{id}', name: 'app_customer_update', methods: ['POST'])]
    public function update(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);
        
        if (!$user) {
            $this->addFlash('error', 'Customer not found');
            return $this->redirectToRoute('app_customers');
        }

        // Validate unique fields (excluding current user)
        $email = $request->request->get('email');
        $phone = $request->request->get('phone');
        $cin = $request->request->get('cin');
        $role = $request->request->get('role');

        // Check if email already exists (excluding current user)
        $existingUserByEmail = $userRepository->findOneBy(['email' => $email]);
        if ($existingUserByEmail && $existingUserByEmail->getId() !== $id) {
            $this->addFlash('error', 'Cet email est déjà utilisé par un autre utilisateur.');
            return $this->redirectToRoute('app_customers');
        }

        // Check if phone already exists (excluding current user)
        $existingUserByPhone = $userRepository->findOneBy(['phone' => $phone]);
        if ($existingUserByPhone && $existingUserByPhone->getId() !== $id) {
            $this->addFlash('error', 'Ce numéro de téléphone est déjà utilisé par un autre utilisateur.');
            return $this->redirectToRoute('app_customers');
        }

        // Check if CIN already exists (for merchant and courier roles, excluding current user)
        if ($role === 'merchant' || $role === 'courier') {
            if ($role === 'merchant') {
                $existingMerchant = $entityManager->getRepository(Merchant::class)->findOneBy(['cin' => $cin]);
                if ($existingMerchant && $existingMerchant->getUser()->getId() !== $id) {
                    $this->addFlash('error', 'Ce CIN est déjà utilisé par un autre commerçant.');
                    return $this->redirectToRoute('app_customers');
                }
            } elseif ($role === 'courier') {
                $existingCourier = $entityManager->getRepository(Courier::class)->findOneBy(['cin' => $cin]);
                if ($existingCourier && $existingCourier->getUser()->getId() !== $id) {
                    $this->addFlash('error', 'Ce CIN est déjà utilisé par un autre livreur.');
                    return $this->redirectToRoute('app_customers');
                }
            }
        }

        // Update user data
        $user->setFirstName($request->request->get('firstName'));
        $user->setLastName($request->request->get('lastName'));
        $user->setEmail($email);
        $user->setPhone($phone);
        $user->setRole($role);

        // Update merchant data if role is merchant
        if ($user->getRole() === 'merchant') {
            $merchant = $entityManager->getRepository(Merchant::class)->findOneBy(['user' => $user]);
            if (!$merchant) {
                $merchant = new Merchant();
                $merchant->setUser($user);
                $entityManager->persist($merchant);
            }
            
            $merchant->setCin($cin);
            $merchant->setBusinessName($request->request->get('businessName'));
            $merchant->setBusinessType($request->request->get('businessType'));
            $merchant->setLocation($request->request->get('location'));
        }

        // Update courier data if role is courier
        if ($user->getRole() === 'courier') {
            $courier = $entityManager->getRepository(Courier::class)->findOneBy(['user' => $user]);
            if (!$courier) {
                $courier = new Courier();
                $courier->setUser($user);
                $entityManager->persist($courier);
            }
            
            $courier->setCin($cin);
            $courier->setLicenseNumber($request->request->get('licenseNumber'));
            $courier->setVehicleType($request->request->get('vehicleType'));
        }

        $entityManager->flush();

        $this->addFlash('success', 'Customer updated successfully');
        return $this->redirectToRoute('app_customers');
    }

    #[Route('/customers/delete/{id}', name: 'app_customer_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->find($id);
        
        if (!$user) {
            $this->addFlash('error', 'Customer not found');
            return $this->redirectToRoute('app_customers');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Customer deleted successfully');
        return $this->redirectToRoute('app_customers');
    }

    #[Route('/customers/merchant-data/{id}', name: 'app_customer_merchant_data', methods: ['GET'])]
    public function getMerchantData(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $userRepository->find($id);
        
        if (!$user || $user->getRole() !== 'merchant') {
            return new JsonResponse(['success' => false, 'message' => 'Merchant not found']);
        }

        $merchant = $entityManager->getRepository(Merchant::class)->findOneBy(['user' => $user]);
        
        if (!$merchant) {
            return new JsonResponse(['success' => false, 'message' => 'Merchant data not found']);
        }

        return new JsonResponse([
            'success' => true,
            'merchant' => [
                'cin' => $merchant->getCin(),
                'businessName' => $merchant->getBusinessName(),
                'businessType' => $merchant->getBusinessType(),
                'location' => $merchant->getLocation()
            ]
        ]);
    }

    #[Route('/customers/courier-data/{id}', name: 'app_customer_courier_data', methods: ['GET'])]
    public function getCourierData(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $userRepository->find($id);
        
        if (!$user || $user->getRole() !== 'courier') {
            return new JsonResponse(['success' => false, 'message' => 'Courier not found']);
        }

        $courier = $entityManager->getRepository(Courier::class)->findOneBy(['user' => $user]);
        
        if (!$courier) {
            return new JsonResponse(['success' => false, 'message' => 'Courier data not found']);
        }

        return new JsonResponse([
            'success' => true,
            'courier' => [
                'cin' => $courier->getCin(),
                'licenseNumber' => $courier->getLicenseNumber(),
                'vehicleType' => $courier->getVehicleType()
            ]
        ]);
    }
}
