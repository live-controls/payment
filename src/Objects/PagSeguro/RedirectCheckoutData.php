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
    public string $inactivateLink;

    public function __construct(string $jsonResponse)
    {
        $responseArr = json_decode($jsonResponse, true);
        $this->id = $responseArr["id"];
        $this->referenceId = $responseArr["reference_id"];
        $this->createdAt = $responseArr["created_at"];
        $this->status = $responseArr["status"];
        $this->customer = []; //TODO: Add this
        $this->customerModifiable = $responseArr["customer_modifiable"];
        $this->items = $responseArr["items"];
        $this->additionalAmount = $responseArr["additional_amount"];
        $this->discountAmount = $responseArr["discount_amount"];
        $this->shipping = []; //TODO: Add this
        $this->paymentMethods = []; //TODO: Add this
        $this->paymentMethodsConfigs = []; //TODO: Add this
        $this->softDescriptor = $responseArr["soft_descriptor"];
        $this->redirectUrl = $responseArr["redirect_url"];
        $this->returnUrl = ""; //TODO: Add this
        $this->notificationUrls = []; //TODO: Add this
        $this->paymentNotificationUrls = []; //TODO: Add this
        $responseLinks = $responseArr["links"];
        foreach($responseLinks as $link)
        {
            if($link["rel"] == "PAY")
            {
                $this->payLink = $link["href"];
            }elseif($link["rel"] == "SELF")
            {
                $this->selfLink = $link["href"];
            }elseif($link["rel"] == "INACTIVATE")
            {
                $this->inactivateLink = $link["href"];
            }
        }
    }

    public function checkoutUrl(bool $offline = false)
    {
        return RedirectCheckout3::getCheckoutUrl($this->id, $offline);
    }
}
