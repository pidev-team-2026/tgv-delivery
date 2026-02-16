<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer des utilisateurs de test
        $users = [
            [
                'name' => 'Jean Dupont',
                'email' => 'jean.dupont@email.com',
            ],
            [
                'name' => 'Marie Martin',
                'email' => 'marie.martin@email.com',
            ],
            [
                'name' => 'Pierre Bernard',
                'email' => 'pierre.bernard@email.com',
            ],
            [
                'name' => 'Sophie Petit',
                'email' => 'sophie.petit@email.com',
            ],
            [
                'name' => 'Thomas Leroy',
                'email' => 'thomas.leroy@email.com',
            ],
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setName($userData['name']);
            $user->setEmail($userData['email']);
            
            $manager->persist($user);
        }

        $manager->flush();
    }
}
