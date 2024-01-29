<?php

namespace LiveControls\Payment\Scripts\PagSeguro;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
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
                'email' => urlencode(config('livecontrols_payment.pagseguro_email_debug',null)),
                'token' => urlencode(config('livecontrols_payment.pagseguro_token_debug',null))
            ];
        }
        return [
            'email' => urlencode(config('livecontrols_payment.pagseguro_email',null)),
            'token' => urlencode(config('livecontrols_payment.pagseguro_token',null))
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
     * @return RedirectCheckoutData
     */
    public static function generateDigitalCode(array $items, PaymentSender|null $sender, string $reference, Carbon|null $expirationDate, string $redirectUrl, array $notificationUrls = [], array $paymentNotificationUrls = [], array|null $paymentMethods = null, int $discount = 0, int $additionalAmount = 0, string $softDescriptor = "", bool $senderRequired = false): RedirectCheckoutData
    {
        if($senderRequired && is_null($sender)){
            throw new Exception('$sender is required but it is null!');
        }

        $client = static::getClient();
        $host = static::getHost();
        $creds = static::getCredentials();
        try{
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
                      "name":"'.$sender->name.'",
                      "email":"'.$sender->email.'",
                      "tax_id":"'.$sender->cpf.'"
                  },' : '').
                  '"items":
                  '.json_encode($items).',
                  '.(is_null($paymentMethods) ? '' : '"payment_methods":
                  '.json_encode($paymentMethods).',').
                  '"discount_amount":'.$discount.',
                  "customer_modifiable":'.!$senderRequired.',
                  "additional_amount":'.$additionalAmount.',
                  "soft_descriptor":"'.$softDescriptor.'",
                  "redirect_url":"'.$redirectUrl.'",'
                  .(is_null($notificationUrls) ? '' : '"notification_urls":
                      '.json_encode($notificationUrls).'
                  ,')
                  .(!is_null($paymentNotificationUrls) ? '"payment_notification_urls":
                      '.json_encode($paymentNotificationUrls).'
                  ' : '').'}',
                'headers' => [
                  'Authorization' => 'Bearer '.$creds["token"],
                  'Content-Type' => 'application/json',
                  'Accept' => 'application/json',
                ],
              ]);
        }catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error("[PagSeguro.RedirectCheckout] ".$e->getResponse()->getBody()->getContents());
            throw new Exception("Internal PagSeguro error!");
        }


        if($response->getStatusCode() != 200 && $response->getStatusCode() != 201){
            throw new Exception($response->getStatusCode().': '.$response->getBody());
        }
        return new RedirectCheckoutData($response->getBody());
    }

    public static function getCheckoutUrl(string $id, bool $offline = false): string
    {
        $token = static::getCredentials()["token"];
        $host = static::getHost();
        $client = static::getClient();
        $response = $client->request('GET', $host.'/checkouts/'.$id, [
            'headers' => [
              'Authorization' => 'Bearer '.$token,
              'accept' => 'application/json',
            ],
        ]);

        if($response->getStatusCode() != 200)
        {
            throw new Exception($response->getStatusCode().': '.$response->getBody());
        }

        //Check for links
        $responseArr = json_decode($response->getBody(), true);
        $responseLinks = $responseArr["links"];
        foreach($responseLinks as $link)
        {
            if($link["rel"] == "PAY")
            {
                return $link["href"];
            }
        }
        throw new Exception('Payment link not found for Checkout '.$id);
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

    /**
     * Compares the signature sent by a webhook
     *
     * @param string $token
     * @param string $payload
     * @param string $authenticityToken
     * @return boolean
     */
    public static function compareSignature(string $token, string $payload, string $authenticityToken):bool{
        $calculatedToken = hash('sha256', $token.'-'.$payload);
        return $calculatedToken == $authenticityToken;
    }

    /**
     * Compares the signature sent by a webhook with the PagSeguro token
     *
     * @param string $payload
     * @param string $authenticityToken
     * @return boolean
     */
    public static function compareSignatureWithToken(string $payload, string $authenticityToken):bool{
        $token = static::getCredentials()["token"];
        return static::compareSignature($token, $payload, $authenticityToken);
    }
}
