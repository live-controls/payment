<?php

namespace LiveControls\Payment\Objects\PagSeguro;

use LiveControls\Payment\Scripts\PagSeguro\RedirectCheckout3;

class RedirectCheckoutData
{
    public string $id;
    public string $referenceId;
    public string $expirationDate;
    public string $createdAt;
    public string $status;
    public array $customer;
    public bool $customerModifiable;
    public array $items;
    public int $additionalAmount;
    public int $discountAmount;
    public array $shipping;
    public array $paymentMethods;
    public array $paymentMethodsConfigs;
    public string $softDescriptor;
    public string $redirectUrl;
    public string $returnUrl;
    public array $notificationUrls;
    public array $paymentNotificationUrls;
    public string $payLink;
    public string $selfLink;
    public string $InactivateLink;

    public function __construct(string $jsonResponse)
    {
        
    }

    public function checkoutUrl()
    {
        return RedirectCheckout3::getCheckoutUrl($this->id);
    }
}