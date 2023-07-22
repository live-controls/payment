<?php

namespace LiveControls\Payment\Objects\Iugu;

class PaymentItem
{
    public readonly string $description;
    public readonly int $quantity;
    public readonly int $priceInCents;

    public function __construct(string $description, int $quantity, int $priceInCents){
        $this->description = $description;
        $this->quantity = $quantity;
        $this->priceInCents = $priceInCents;
    }
}