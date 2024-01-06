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
        $responseArr = json_decode($jsonResponse);
        $this->id = $responseArr["id"];
        $this->referenceId = $responseArr["reference_id"];
        $this->createdAt = $responseArr["created_at"];
        $this->status = $responseArr["status"];
        $this->customer = null; //TODO: Add this
        $this->customerModifiable = $responseArr["customer_modifiable"];
        $this->items = json_decode($responseArr["items"]);
        $this->additionalAmount = $responseArr["additional_amount"];
        $this->discountAmount = $responseArr["discount_amount"];
        $this->shipping = null; //TODO: Add this
        $this->paymentMethods = null; //TODO: Add this
        $this->paymentMethodsConfigs = null; //TODO: Add this
        $this->softDescriptor = $responseArr["soft_descriptor"];
        $this->redirectUrl = $responseArr["redirect_url"];
        $this->returnUrl = null; //TODO: Add this
        $this->notificationUrls = null; //TODO: Add this
        $this->paymentNotificationUrls = null; //TODO: Add this
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
