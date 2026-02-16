<?php

namespace App\Form\Model;

class UserRegistrationData
{
    public ?string $firstName = null;
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