<?php

namespace LiveControls\Payment\Objects\PagSeguro;

class PaymentSender{
    public readonly string $name;
    public readonly string $email;
    public readonly string $phone_ddd;
    public readonly string $phone;
    public readonly int $cpf;

    public function __construct(string $name, string $email, string $phone_ddd, string $phone, int $cpf)
    {
        $this->name = $name;
        $this->email = $email;
        $this->phone_ddd = $phone_ddd;
        $this->phone = $phone;
        $this->cpf = $cpf;
    }
}