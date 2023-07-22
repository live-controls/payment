<?php

namespace LiveControls\Payment\Scripts\Iugu;

use Carbon\Carbon;
use Exception;
use LiveControls\Payment\Objects\Iugu\PaymentSender;
use Illuminate\Support\Facades\Log;

class TransparentCheckout
{
    /**
     * Returns the right IUGU token from the environment file
     * @return string IUGU Token
     */
    private static function getKey():string
    {

        //Check if the server is running in debug mode and use the test key in that case
        if(config('app.debug'))
        {
            //Is in Debug-Mode
            $api_key = env('IUGU_TEST_TOKEN');
        }else{
            //Is in Live-Mode
            $api_key = env('IUGU_LIVE_TOKEN');
        }
        return $api_key;
    }

    /**
     * Returns a new GuzzleHttp Client
     * @return \GuzzleHttp\Client
     */
    private static function getClient(): \GuzzleHttp\Client
    {
        $client = new \GuzzleHttp\Client();
        return $client;
    }

    public static function createPix(array $paymentItems, PaymentSender $paymentSender,
    string $orderId, Carbon $dueDate, string $returnUrl, string $expiredUrl, int $discountInCents = 0): mixed
    {
        $key = static::getKey();
        $client = static::getClient();

        $paymentItemsStr = "[";
        foreach($paymentItems as $paymentItem){
            $paymentItemsStr .= '{
                "description":"'.$paymentItem->description.'",
                "quantity":'.$paymentItem->quantity.',
                "price_cents":'.$paymentItem->priceInCents.'
            }'."\n";
        }
        $paymentItemsStr .= "]";

        $response = $client->request('POST', 'https://api.iugu.com/v1/invoices?api_token='.$key, [
            'body' => '{
                "ensure_workday_due_date":true,
                "discount_cents":'.$discountInCents.',
                "items":'.$paymentItemsStr.',
                    "payable_with": ["pix"],
                    "payer":{
                        "address":
                        {
                            "zip_code":"'.$paymentSender->zipCode.'",
                            "street":"'.$paymentSender->street.'",
                            "number":"'.$paymentSender->number.'",
                            "district":"'.$paymentSender->district.'",
                            "city":"'.$paymentSender->city.'",
                            "state":"'.$paymentSender->state.'",
                            "complement":"'.$paymentSender->complement.'",
                            "country":"'.$paymentSender->country.'"
                        },
                        "cpf_cnpj":"'.$paymentSender->cpfCnpj.'",
                        "name":"'.$paymentSender->name.'",
                        "phone_prefix":"'.$paymentSender->phonePrefix.'",
                        "email":"'.$paymentSender->email.'"
                    },
                    "email":"'.$paymentSender->email.'",
                    "due_date":"'.$dueDate->format('Y-m-d').'",
                    "return_url":"'.$returnUrl.'",
                    "expired_url":"'.$expiredUrl.'",
                    "order_id":"'.$orderId.'"
                }',

            'headers' => [
              'Accept' => 'application/json',
              'Content-Type' => 'application/json',
            ],

          ]);

          $return = [];
          if($response->getStatusCode() === 400)
          {
              //Fetch errors
              $return["status"] = 400;
              $return["errors"] = json_decode($response->getBody())->errors;
              return $return;
          }elseif($response->getStatusCode() === 422)
          {
              //Fetch due_date
              $return["status"] = 422;
              $return["due_date"] = json_decode($response->getBody())->due_date;
              return $return;
          }elseif($response->getStatusCode() != 200)
          {
              $return["status"] = $response->getStatusCode();
              $return["body"] = $response->getBody();
              return $return;
          }

          $return["status"] = 200;
          $return["json"] = json_decode($response->getBody());

          return $return;
    }

    public static function createCreditCard(array $paymentItems, string $cctoken, int $parcels, string $payerEmail, string $orderId)
    {
        if($parcels < 2 || $parcels > 12)
        {
            $parcels = 1;
        }

        $key = static::getKey();
        $client = static::getClient();

        $paymentItemsStr = "[";
        foreach($paymentItems as $paymentItem){
            $paymentItemsStr .= '{
                "description":"'.$paymentItem->description.'",
                "quantity":'.$paymentItem->quantity.',
                "price_cents":'.$paymentItem->priceInCents.'
            }'."\n";
        }
        $paymentItemsStr .= "]";
        

        $response = $client->request('POST', 'https://api.iugu.com/v1/charge?api_token='.$key, [

            'body' => '{
                "items":'.$paymentItemsStr.',
                "email":"'.$payerEmail.'",
                "token":"'.$cctoken.'",
                "months":'.$parcels.',
                "order_id":"'.$orderId.'"
            }',

            'headers' => [
              'Accept' => 'application/json',
              'Content-Type' => 'application/json',
            ],

        ]);

        $return = [];
          if($response->getStatusCode() === 400)
          {
              //Fetch errors
              $return["status"] = 400;
              $return["errors"] = json_decode($response->getBody())->errors;
              return $return;
          }elseif($response->getStatusCode() === 422)
          {
              //Fetch due_date
              $return["status"] = 422;
              $return["due_date"] = json_decode($response->getBody())->due_date;
              return $return;
          }elseif($response->getStatusCode() != 200)
          {
              $return["status"] = $response->getStatusCode();
              $return["body"] = $response->getBody();
              return $return;
          }

          $return["status"] = 200;
          $return["json"] = json_decode($response->getBody());
          return $return;
    }

    public static function createBankSlip(array $paymentItems, PaymentSender $paymentSender, Carbon $dueDate, 
    string $orderId, string $returnUrl, string $expiredUrl, int $latePaymentFineInCents = 0, int $perDayInterestInCents = 0, 
    int $discountInCents = 0)
    {
        $key = static::getKey();
        $client = static::getClient();

        $hasjuros = $latePaymentFineInCents > 0 ? 'true' : 'false';
        $hasjuros = $perDayInterestInCents > 0 ? 'true' : $hasjuros;
        $hasPerDayInterest = $perDayInterestInCents > 0 ? 'true' : 'false';

        $paymentItemsStr = "[";
        foreach($paymentItems as $paymentItem){
            $paymentItemsStr .= '{
                "description":"'.$paymentItem->description.'",
                "quantity":'.$paymentItem->quantity.',
                "price_cents":'.$paymentItem->priceInCents.'
            }'."\n";
        }
        $paymentItemsStr .= "]";

        $response = $client->request('POST', 'https://api.iugu.com/v1/invoices?api_token='.$key, [
            'body' => '{
                "ensure_workday_due_date":true,
                "discount_cents":'.$discountInCents.',
                "fines":'.$hasjuros.',
                "late_payment_fine_cents":'.$latePaymentFineInCents.',
                "per_day_interest":'.$hasPerDayInterest.',
                "per_day_interest_cents":'.$perDayInterestInCents.',
                "items":'.$paymentItemsStr.',
                    "payable_with":["bank_slip"],
                    "payer":{
                        "address":
                        {
                            "zip_code":"'.$paymentSender->zipCode.'",
                            "street":"'.$paymentSender->street.'",
                            "number":"'.$paymentSender->number.'",
                            "district":"'.$paymentSender->district.'",
                            "city":"'.$paymentSender->city.'",
                            "state":"'.$paymentSender->state.'",
                            "complement":"'.$paymentSender->complement.'",
                            "country":"'.$paymentSender->country.'"
                        },
                        "cpf_cnpj":"'.$paymentSender->cpfCnpj.'",
                        "name":"'.$paymentSender->name.'",
                        "phone_prefix":"'.$paymentSender->phonePrefix.'",
                        "email":"'.$paymentSender->email.'"
                    },
                    "email":"'.$paymentSender->email.'",
                    "due_date":"'.$dueDate->format('Y-m-d').'",
                    "return_url":"'.$returnUrl.'",
                    "expired_url":"'.$expiredUrl.'",
                    "order_id":"'.$orderId.'"
                }',

            'headers' => [
              'Accept' => 'application/json',
              'Content-Type' => 'application/json',
            ],

          ]);

          $return = [];
          if($response->getStatusCode() === 400)
          {
              //Fetch errors
              $return["status"] = 400;
              $return["errors"] = json_decode($response->getBody())->errors;
              return $return;
          }elseif($response->getStatusCode() === 422)
          {
              //Fetch due_date
              $return["status"] = 422;
              $return["due_date"] = json_decode($response->getBody())->due_date;
              return $return;
          }elseif($response->getStatusCode() != 200)
          {
              $return["status"] = $response->getStatusCode();
              $return["body"] = $response->getBody();
              return $return;
          }

          $return["status"] = 200;
          $return["json"] = json_decode($response->getBody());
          return $return;
    }

    public static function removePayment(string $faturaid):bool
    {
        $key = static::getKey();
        $client = static::getClient();
        try{
            $response = $client->request('PUT', 'https://api.iugu.com/v1/invoices/'.$faturaid.'/cancel?api_token='.$key, [
                'headers' => [
                  'Accept' => 'application/json',
                ],
            ]);
        }catch(Exception $ex){
            Log::warning("Couldn\'t remove payment ".$faturaid." => ".$ex->getMessage());
            return false;
        }

        if($response->getStatusCode() === 200)
        {
            return true;
        }
        return false;
    }

    /**
     * Gets the Information about a Boleto from IUGU
     *
     * @param string $id The "fatura_id" on IUGU
     * @return mixed
     */
    public static function getPaymentInfo(string $id): mixed
    {
        $key = static::getKey();
        $client = static::getClient();

        $response = $client->request('GET', 'https://api.iugu.com/v1/invoices/'.$id.'?api_token='.$key, [
            'headers' => [
              'Accept' => 'application/json',
            ],
          ]);

        return json_decode($response->getBody());
    }

    /**
     * Reads out all Boletos from $start to $limit. Limit is 100 per request.
     *
     * @param integer $start The start index of the boleto
     * @param integer $limit
     * @return object
     */
    public static function getPayments(int $start = 0, int $limit = 100, Carbon $updatedSince, string $statusfilter = null): object
    {
        $key = static::getKey();
        $client = static::getClient();

        $updatedSince = $updatedSince->format('Y-m-dTh:i:s-03:00');

        if($statusfilter != null)
        {
            $response = $client->request('GET', 'https://api.iugu.com/v1/invoices?updated_since='.$updatedSince.'&limit='.$limit.'&start='.$start.'&api_token='.$key, [
                'headers' => [
                  'Accept' => 'application/json',
                ],
            ]);
        }else{
            $response = $client->request('GET', 'https://api.iugu.com/v1/invoices?updated_since='.$updatedSince.'&limit='.$limit.'&start='.$start.'&api_token='.$key, [
                'headers' => [
                  'Accept' => 'application/json',
                ],
            ]);
        }

        if($response->getStatusCode() == 200)
        {
            return json_decode($response->getBody());
        }
    }

    /**
     * Sends the boleto to the email set in it.
     *
     * @param string $id The id of the fatura
     * @return boolean
     */
    public static function sendBankSlipPerMail(string $id): bool
    {
        $key = static::getKey();
        $client = static::getClient();

        $response = $client->request('POST', 'https://api.iugu.com/v1/invoices/'.$id.'/send_email?api_token='.$key, [
            'headers' => [
              'Accept' => 'application/json',
            ],
        ]);

        return $response->getStatusCode() == 200 ? true : false;
    }

    public static function getBankSlipPDF(string $id): string|false
    {
        $key = static::getKey();
        $client = static::getClient();

        $response = $client->request('GET', 'https://api.iugu.com/v1/invoices/'.$id.'?api_token='.$key, [
            'headers' => [
              'Accept' => 'application/json',
            ],
        ]);

        if($response->getStatusCode() != 200)
        {
            return false;
        }

        $responseobj = json_decode($response->getBody());
        return $responseobj->secure_url.'.pdf';
    }

    /**
     * Update a Boleto with the new informations
     *
     * @param array $paymentItems An array of PaymentItem objects
     * @param string $paymentId The ID of the payment
     * @param Carbon $duedate The duedate, is required and needs to be in the future
     * @param integer $valuecents The value in cents
     * @param boolean $ignorecanceled Ignora o envio do e-mail de cancelamento da fatura atual
     * @param boolean $ignoredue Ignora o envio do e-mail de cobrança da nova fatura
     * @param boolean $currentfines Caso true, aplica multas da fatura original à segunda via
     * @param boolean $earlydiscount Caso true, copia os descontos de pagamento antecipado da fatura original para a 2ª via
     * @return string|false Returns the body or false if not valid
     */
    public static function updatePayment(array $paymentItems, string $paymentId, Carbon $duedate, bool $ignorecanceled = true, bool $ignoredue = true, bool $currentfines = true, bool $earlydiscount = true): string|false
    {
        $key = static::getKey();
        $client = static::getClient();

        if(count($paymentItems) > 0)
        {
            $paymentItemsStr = "[";
            foreach($paymentItems as $paymentItem){
                $paymentItemsStr .= '{
                    "description":"'.$paymentItem->description.'",
                    "quantity":'.$paymentItem->quantity.',
                    "price_cents":'.$paymentItem->priceInCents.'
                }'."\n";
            }
            $paymentItemsStr .= "]";
            
            //If the value is changed, change the item (only first one in this case)
            $response = $client->request('POST', 'https://api.iugu.com/v1/invoices/'.$paymentId.'/duplicate?api_token='.$key, [
                'headers' => [
                  'Accept' => 'application/json',
                  'Content-Type' => 'application/json',
                ],
                'body' => '{
                    "due_date":"'.$duedate->format('Y-m-d').'",
                    "ignore_canceled_email":'.($ignorecanceled ? 'true' : 'false').',
                    "ignore_due_email":'.($ignoredue ? 'true' : 'false').',
                    "current_fines_option":'.($currentfines ? 'true' : 'false').',
                    "keep_early_payment_discount":'.($earlydiscount ? 'true' : 'false').',
                    "items":'.$paymentItemsStr.'
                }'
            ]);
        }else{
            //If the value isn't changed, don't change any items
            $response = $client->request('POST', 'https://api.iugu.com/v1/invoices/'.$id.'/duplicate?api_token='.$key, [
                'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                ],
                'body' => '{
                    "due_date":"'.$duedate->format('Y-m-d').'",
                    "ignore_canceled_email":'.($ignorecanceled ? 'true' : 'false').',
                    "ignore_due_email":'.($ignoredue ? 'true' : 'false').',
                    "current_fines_option":'.($currentfines ? 'true' : 'false').',
                    "keep_early_payment_discount":'.($earlydiscount ? 'true' : 'false').'
                }'
            ]);
        }

        if($response->getStatusCode() != 200)
        {
            return false;
        }

        return $response->getBody();
    }
}
