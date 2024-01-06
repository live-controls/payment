<?php

namespace LiveControls\Payment\Objects\PagSeguro;

/**
 * @deprecated 2.0 Not needed in RedirectCheckout3, use this instead!
 */
class PaymentReceiver
{
    public readonly string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }
}