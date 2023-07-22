<?php

namespace LiveControls\Payment\Objects\PagSeguro;

class ShippingInformation
{
    public readonly string $road;
    public readonly string $number;
    public readonly string $complement;
    public readonly string $area;
    public readonly string $city;
    public readonly string $state;
    public readonly int $cep;

    public readonly int $shippingType;
    public readonly float $shippingCost;
    
    public readonly bool $addressRequired;
    
    public function __construct(string $road, string $number, string $complement, string $area, string $city, string $state, int $cep, int $shippingType, float|int $shippingCost, bool $addressRequired)
    {
        $this->road = $road;
        $this->number = $number;
        $this->complement = $complement;
        $this->area = $area;
        $this->city = $city;
        $this->state = $state;
        $this->cep = $cep;

        $this->shippingType = $shippingType;
        $this->shippingCost = $shippingCost;

        $this->addressRequired = $addressRequired;
    }
}