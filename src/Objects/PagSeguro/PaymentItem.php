<?php

namespace LiveControls\Payment\Objects\PagSeguro;

class PaymentItem{
    /**
     * PaymentItem to use in RedirectCheckout
     *
     * @param string $referenceId
     * @param string $name
     * @param string $description
     * @param integer $quantity
     * @param integer $unitAmount
     */
    public function __construct(public readonly string $referenceId, public readonly string $name, public readonly string $description, public readonly int $quantity, public readonly int $unitAmount){}
}