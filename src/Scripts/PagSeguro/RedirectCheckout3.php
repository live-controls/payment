<?php

namespace LiveControls\Payment\Scripts\PagSeguro;

use Carbon\Carbon;
use Exception;
use LiveControls\Payment\Objects\PagSeguro\PaymentItem;
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
          return 'https://sandbox.api.pagseguro.com';
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
    public static function generateDigitalCode(array $items, PaymentSender|null $sender, string $reference, Carbon|null $expirationDate, string $redirectUrl, array $notificationUrls = [], array $paymentNotificationUrls = [], array|null $paymentMethods = null, int $discount = 0, int $additionalAmount = 0, string $softDescriptor = "", bool $senderRequired = false):RedirectCheckoutData|false
    {
        if($senderRequired && is_null($sender)){
            throw new Exception('$sender is required but it is null!');
        }

        $itemStr = "";
        foreach($items as $item)
        {
            $itemStr .= '{"reference_id":"'.$item->referenceId.'",';
            $itemStr .= '"name":"'.$item->name.'",';
            $itemStr .= '"description":"'.$item->description.'",';
            $itemStr .= '"quantity":'.$item->quantity.',';
            $itemStr .= '"unit_amount":'.$item->unitAmount.'},';  
        }

        $paymentMethodsStr = "";
        if(is_array($paymentMethods)){
            foreach($paymentMethods as $pm)
            {
                $paymentMethodsStr .= "{";
                $paymentMethodsStr .= '"type": "'.$pm->type.'",';
                if(!is_null($pm->brands) && is_array($pm->brands))
                {
                    $paymentMethodsStr .= '"brands": [';
                    foreach($pm->brands as $pmBrand){
                        $paymentMethodsStr .= '"'.$pmBrand.'",';
                    }
                    $paymentMethodsStr .= "]";
                }
                $paymentMethodsStr .= "},";
            }
        }

        $notificationUrlsStr = "";
        if(is_array($notificationUrls)){
            foreach($notificationUrls as $nu)
            {
                $notificationUrlsStr .= '"'.$nu.'",';
            }
        }

        $paymentNotificationUrlsStr = "";
        if(is_array($paymentNotificationUrls)){
            foreach($paymentNotificationUrls as $pnu)
            {
                $paymentNotificationUrlsStr .= '"'.$pnu.'",';
            }
        }

        $client = static::getClient();
        $host = static::getHost();
        $creds = static::getCredentials();
        $response = $client->request('POST', $host.'/checkouts', [
          'body' => '{
            "reference_id": "'.$reference.'",
            '.(!is_null($expirationDate) ? '"expiration_date": "'.$expirationDate->toIso8601String().'",' : '').
            (!is_null($sender) ? '"customer":
            {
                "phone":
                {
                    "country":"'.$sender->phoneCountry.'",
                    "area":"'.$sender->phoneDdd.'",
                    "number":"'.$sender->phone.'"
                },
                "Name":"'.$sender->name.'",
                "email":"'.$sender->email.'",
                "tax_id":"'.$sender->cpf.'"
            },' : '').
            '"items":
            [
                '.$itemStr.'
            ],
            '.(is_null($paymentMethods) ? '' : '"payment_methods":
            [
                '.$paymentMethodsStr.'
            ],').
            '"discount_amount":'.number_format($discount,2,'.','').',
            "customer_modifiable":'.!$senderRequired.',
            "additional_amount":'.$additionalAmount.',
            "soft_descriptor":"'.$softDescriptor.'",
            "redirect_url":"'.$redirectUrl.'",
            "notification_urls":[
                '.$notificationUrlsStr.'
            ],
            "payment_notification_urls":[
                '.$paymentNotificationUrlsStr.'
            ]}',
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
    public static function generatePhysicalCode(array $items, PaymentSender|null $sender, ShippingInformation $shippingInformation, string $reference, string $redirectUrl, int $timeout = 60, int $maxAge = 30, int $maxUses = 1, bool $enableRecover = false, int $discount = 0):array|false
    {
        throw new Exception('Method not implemented');
    }

    public static function getTransactionInformation(string $transactionCode): array|false{
        throw new Exception('Method not implemented');
    }

    public static function getTransactions(Carbon $from, Carbon $to):array{
        throw new Exception('Method not implemented');
    }

    public static function reverseTransaction(string $transactionCode, float $amount): bool{
        throw new Exception('Method not implemented');
    }

    public static function cancelTransaction(string $transactionCode){
        throw new Exception('Method not implemented');
    }


}