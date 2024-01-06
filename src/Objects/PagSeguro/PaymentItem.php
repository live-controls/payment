<?php

namespace LiveControls\Payment\Objects\PagSeguro;

class PaymentItem{
    /**
     * PaymentItem to use in RedirectCheckout
     *
     * @param string $reference_id
     * @param string $name
     * @param string $description
     * @param integer $quantity
     * @param integer $unit_amount
     */
    public function __construct(public readonly string $reference_id, public readonly string $name, public readonly string $description, public readonly int $quantity, public readonly int $unit_amount){}
}
