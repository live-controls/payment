<?php

namespace LiveControls\Payment\Objects\PagSeguro;

class PaymentReceiver
{
    public readonly string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }
}