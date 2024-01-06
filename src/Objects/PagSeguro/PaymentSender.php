<?php

namespace LiveControls\Payment\Objects\PagSeguro;

class PaymentSender{
    /**
     * PaymentItem to use in RedirectCheckout
     *
     * @param string $name
     * @param string $email
     * @param string $phoneCountry
     * @param string $phoneDdd
     * @param string $phone
     * @param integer $cpf
     */
    public function __construct(public readonly string $name, public readonly string $email, public readonly string $phoneCountry, public readonly string $phoneDdd, public readonly string $phone, public readonly int $cpf){}
}