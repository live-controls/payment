<?php

namespace LiveControls\Payment\Objects\PagSeguro;

class PaymentItem{
    public readonly string $id;
    public readonly string $description;
    public readonly float $amount;
    public readonly int $quantity;
    public readonly float $weight;
    public readonly float $shippingCost;

    public function __construct(string $id, string $description, float|int $amount, int $quantity, float|int $weight, float|int $shippingCost)
    {
        $this->id = $id;
        $this->description = $description;
        $this->amount = $amount;
        $this->quantity = $quantity;
        $this->weight = $weight;
        $this->shippingCost = $shippingCost;
    }
}