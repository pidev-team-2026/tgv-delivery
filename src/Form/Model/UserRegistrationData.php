<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class UserRegistrationData
{
    #[Assert\NotBlank(message: 'First name is required')]
    #[Assert\Length(min: 2, max: 50)]
    #[Assert\Regex(
        pattern: '/^[A-Za-z\s\-]+$/',
        message: 'First name must contain only letters'
    )]
    public ?string $firstName = null;

    #[Assert\NotBlank(message: 'Last name is required')]
    #[Assert\Length(min: 2, max: 50)]
    #[Assert\Regex(
        pattern: '/^[A-Za-z\s\-]+$/',
        message: 'Last name must contain only letters'
    )]
    public ?string $lastName = null;

    // already existing fields 👇
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $password = null;
    public ?string $confirmPassword = null;

    // Merchant
    public ?string $cin = null;
    public ?string $businessName = null;
    public ?string $businessType = null;
    public ?string $location = null;

    // Courier
    public ?string $licenseNumber = null;
    public ?string $vehicleType = null;
}

?>