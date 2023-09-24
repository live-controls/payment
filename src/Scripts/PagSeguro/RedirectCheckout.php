<?php

namespace LiveControls\Payment\Scripts\PagSeguro;

use Carbon\Carbon;
use Exception;
use LiveControls\Payment\Objects\PagSeguro\PaymentReceiver;
use LiveControls\Payment\Objects\PagSeguro\PaymentSender;
use LiveControls\Payment\Objects\PagSeguro\ShippingInformation;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class RedirectCheckout{

  
    /**
     * Returns an array with 'email' and 'token' depending if the application is in debug mode or not
     *
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

    private static function getHost(bool $withWs = false):string{
      if(config('app.debug')){
        return 'https://'.($withWs ? 'ws.' : '').'sandbox.pagseguro.uol.com.br/v2/';
      }
      return 'https://pagseguro.uol.com.br/v2/';
    }

    private static function getClient(): \GuzzleHttp\Client
    {
        $client = new \GuzzleHttp\Client();
        return $client;
    }

    public static function generateCode(array $items, PaymentReceiver $receiver, PaymentSender $sender, ShippingInformation $shippingInformation, string $reference, string $redirectUrl, int $timeout = 60, int $maxAge = 30, int $maxUses = 1, bool $enableRecover = false, int $discount = 0):SimpleXMLElement|false{
        $credentials = static::getCredentials();
        $client = static::getClient();

        $itemsStr = '<items>';
        foreach($items as $item){
            $itemsStr .= '<item>
            <id>'.$item->id.'</id>
            <description>'.$item->description.'</description>
            <amount>'.number_format($item->amount,2,'.','').'</amount>
            <quantity>'.$item->quantity.'</quantity>
            <weight>'.$item->weight.'</weight>
            <shippingCost>'.number_format($item->shippingCost,2,'.','').'</shippingCost>
            </item>';
        }
        $itemsStr .= '</items>';

        try {
            $response = $client->request('POST', static::getHost(true).'checkout?email='.$credentials["email"].'&token='.$credentials["token"], [
                'body' => '<checkout>
                <sender>
                  <name>'.$sender->name.'</name>
                  <email>'.$sender->email.'</email>
                  <phone>
                    <areaCode>'.$sender->phone_ddd.'</areaCode>
                    <number>'.$sender->phone.'</number>
                  </phone>
                  <documents>
                    <document>
                      <type>CPF</type>
                      <value>'.$sender->cpf.'</value>
                    </document>
                  </documents>
                </sender>
                <currency>BRL</currency>
                '.$itemsStr.'
                <redirectURL>'.$redirectUrl.'</redirectURL>
                <extraAmount>'.number_format($discount,2,'.','').'</extraAmount>
                <reference>'.$reference.'</reference>
                <shipping>
                  <address>
                    <street>'.$shippingInformation->road.'</street>
                    <number>'.$shippingInformation->number.'</number>
                    <complement>'.$shippingInformation->complement.'</complement>
                    <district>'.$shippingInformation->area.'</district>
                    <city>'.$shippingInformation->city.'</city>
                    <state>'.$shippingInformation->state.'</state>
                    <country>BRA</country>
                    <postalCode>'.$shippingInformation->cep.'</postalCode>
                  </address>
                  <type>'.$shippingInformation->shippingType.'</type>
                  <cost>'.number_format($shippingInformation->shippingCost,2,'.','').'</cost>
                  <addressRequired>'.($shippingInformation->addressRequired ? 'true' : 'false').'</addressRequired>
                </shipping>
                <timeout>'.$timeout.'</timeout>
                <maxAge>'.$maxAge.'</maxAge>
                <maxUses>'.$maxUses.'</maxUses>
                <receiver>
                  <email>'.$receiver->email.'</email>
                </receiver>
                <enableRecover>'.($enableRecover ? 'true' : 'false').'</enableRecover>
              </checkout>
              ',
                'headers' => [
                  'Content-Type' => 'application/xml; charset=ISO-8859-1',
                  'Accept' => 'application/xml; charset=ISO-8859-1',
                ],
              ]);
            if($response->getStatusCode() == 200){
                //CODE GENERATED
                $sxml = simplexml_load_string($response->getBody());
                return $sxml;
            }else{
                throw new Exception('Invalid PagSeguro Statuscode! => '.$response->getStatusCode());
            }
        }catch (Exception $ex){
            throw $ex;
        }
        return false;
    }

    public static function getTransactionInformation(string $transactionCode): SimpleXMLElement|false{
        $credentials = static::getCredentials();
        $client = static::getClient();

        try {
            $response = $client->request('GET', static::getHost(true).'transactions/'.$transactionCode.'?email='.$credentials["email"].'&token='.$credentials["token"], [
                'headers' => [
                  'Accept' => 'application/xml; charset=ISO-8859-1',
                  'content-type' => 'application/json',
                ],
              ]);

            if($response->getStatusCode() == 200){
                $sxml = simplexml_load_string($response->getBody());
                return $sxml;
            }elseif($response->getStatusCode() == 404){
                return false;
            }else{
                throw new Exception("Responds with status ".$response->getStatusCode());
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return false;
        }
        return false;
    }

    public static function getTransactions(Carbon $from, Carbon $to):array{
        $credentials = static::getCredentials();
        $client = static::getClient();

        if($to->timestamp > Carbon::now()->timestamp){
            $to = Carbon::now()->subHours(3);
        }
        $response = $client->request('GET', static::getHost(true).'transactions?email='.$credentials["email"].'&token='.$credentials["token"].'&initialDate='.$from->format(DATE_W3C).'&finalDate='.$to->format(DATE_W3C), [
            'headers' => [
              'Accept' => 'application/xml; charset=ISO-8859-1',
              'content-type' => 'application/json',
            ],
          ]);

        if($response->getStatusCode() == 200){
            $sxml = simplexml_load_string($response->getBody());
            $transactions = [];
            foreach($sxml->transactions as $transaction){
                array_push($transactions, $transaction->transaction);
            }
            return $transactions;
        }else{
            throw new Exception("Status code is ".$response->getStatusCode());
        }
        return [];
    }

    public static function reverseTransaction(string $transactionCode, float $amount): bool{
        $credentials = static::getCredentials();
        $client = static::getClient();

        $response = $client->request('POST', static::getHost(true).'transactions/refunds?email='.$credentials["email"].'&token='.$credentials["token"], [
          'body' => '{
            "transactionCode":"'.$transactionCode.'",
            "refundValue":'.number_format($amount,2,'.','').'
          }',
          'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/xml; charset=ISO-8859-1',
          ],
        ]);
        if($response->getStatusCode() == 200){
            return true;
        }else{
            return false;
        }
    }

    public static function cancelTransaction(string $transactionCode){
        $credentials = static::getCredentials();
          $client = static::getClient();

          $response = $client->request('POST', static::getHost(true).'transactions/cancels?email='.$credentials["email"].'&token='.$credentials["token"], [
            'body' => '{
              "transactionCode":"'.$transactionCode.'"
            }',
            'headers' => [
              'Content-Type' => 'application/json',
              'Accept' => 'application/xml; charset=ISO-8859-1',
            ],
          ]);
          if($response->getStatusCode() == 200){
              return true;
          }else{
              return false;
          }
    }
}