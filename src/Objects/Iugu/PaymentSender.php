<?php

namespace LiveControls\Payment\Objects\Iugu;

class PaymentSender
{
    public readonly string $name;
    public readonly string $cpfCnpj;
    public readonly int $phonePrefix;
    public readonly int $phone;
    public readonly string $street;
    public readonly string $number;
    public readonly string $district;
    public readonly string $city;
    public readonly string $state;
    public readonly string $zipCode;
    public readonly string $complement;
    public readonly string $country;
    public readonly string $email;

    public function __construct(string $name, string $cpfCnpj, string $email, int $phonePrefix, int $phone, string $street, string $number, string $complement, string $district, string $city, string $state, string $zipCode, string $country){
        $this->name = $name;
        $this->cpfCnpj = $cpfCnpj;
        $this->phonePrefix = $phonePrefix;
        $this->phone = $phone;
        $this->street = $street;
        $this->number = $number;
        $this->district = $district;
        $this->city = $city;
        $this->state = $state;
        $this->zipCode = $zipCode;
        $this->complement = $complement;
        $this->country = $country;
        $this->email = $email;
    }
}