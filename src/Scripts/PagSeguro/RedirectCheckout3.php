<?php

namespace LiveControls\Payment\Scripts\PagSeguro;

use Exception;
use LiveControls\Payment\Objects\PagSeguro\PaymentReceiver;
use LiveControls\Payment\Objects\PagSeguro\PaymentSender;
use LiveControls\Payment\Objects\PagSeguro\RedirectCheckoutData;
use LiveControls\Payment\Objects\PagSeguro\ShippingInformation;

/**
 * Redirect Checkout for PagSeguro API Version 3
 */
class RedirectCheckout3
{
    /**
     * Returns an array with 'email' and 'token' depending if the application is in debug mode or not
     * @return array
     */
    private static function getCredentials():array{
        if(config('app.debug')){
            return [
                'email' => urlencode(env('PAGSEGURO_EMAIL_DEBUG')),
                'token' => urlencode(env('PAGSEGURO_TOKEN_DEBUG'))
            ];
        }
        return [
            'email' => urlencode(env('PAGSEGURO_EMAIL')),
            'token' => urlencode(env('PAGSEGURO_TOKEN'))
        ];
    }

    /**
     * Returns the host Url of PagSeguro depending if it's in debug mode or not
     * @return string PagSeguro Url
     */
    private static function getHost():string{
        if(config('app.debug')){
          return 'https://sandbox.api.pagseguro.com/';
        }
        return 'https://api.pagseguro.com';
    }

    /**
     * Returns a new GuzzleHttp client
     *
     * @return \GuzzleHttp\Client
     */
    private static function getClient(): \GuzzleHttp\Client
    {
        $client = new \GuzzleHttp\Client();
        return $client;
    }

    /**
     * Generates a new checkout code for a digital object (without shipping costs)
     *
     * @return array
     */
    public static function generateDigitalCode(array $items, PaymentReceiver $receiver, PaymentSender|null $sender, string $reference, string $redirectUrl, array $paymentMethods, int $timeout = 60, int $maxAge = 30, int $maxUses = 1, bool $enableRecover = false, int $discount = 0, bool $senderRequired = false):RedirectCheckoutData|false
    {
        if($senderRequired && is_null($sender)){
            throw new Exception('$sender is required but it is null!');
        }
        $client = static::getClient();
        $host = static::getHost();
        $creds = static::getCredentials();
        $response = $client->request('POST', $host.'/checkouts', [
          'body' => '{
            "customer":
            {
                "phone":
                {
                    "country":"+55",
                    "area":"27",
                    "number":"999999999"
                },
                "Name":"Max",
                "email":"joao@teste.com",
                "tax_id":"00000000000"
            },
            "items":
            {
                "reference_id":"REFERÊNCIA DO PRODUTO",
                "name":"Test Product",
                "description":"Description",
                "quantity":1,
                "unit_amount":100
            },
            "payment_methods":
            {
                "type":"CREDIT_CARD"
            },
            "discount_amount":1,
            "reference_id":"1234",
            "expiration_date":"2023-08-14T19:09:10-03:00",
            "customer_modifiable":true,
            "additional_amount":0,
            "soft_descriptor":"xxxx",
            "redirect_url":"https://pagseguro.uol.com.br",
            "notification_urls":["https://pagseguro.uol.com.br"],
            "payment_notification_urls":"https://pagseguro.uol.com.br"}',
          'headers' => [
            'Authorization' => $creds["token"],
            'Content-type' => 'application/json',
            'accept' => 'application/json',
          ],
        ]);
        

        if($response->getStatusCode() != 200){
            throw new Exception($response->getStatusCode().': '.$response->getBody());
        }
        return new RedirectCheckoutData($response->getBody());
    }

    /**
     * Generates a new checkout code for a physical object (with shipping costs)
     *
     * @return void
     */
    public static function generatePhysicalCode(array $items, PaymentReceiver|null $receiver, PaymentSender|null $sender, ShippingInformation $shippingInformation, string $reference, string $redirectUrl, int $timeout = 60, int $maxAge = 30, int $maxUses = 1, bool $enableRecover = false, int $discount = 0):array|false
    {
        throw new Exception('Method not implemented');
    }




}