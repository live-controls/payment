<?php
namespace LiveControls\Payment\Objects\PagSeguro;


enum ACCEPTED_PAGSEGURO_BRANDS
{
   case AMEX;
   case AVISTA;
   case AURA;
   case BANESECARD;
   case BRASILCARD;
   case CABAL;
   case CARDBAN;
   case DINERS;
   case DISCOVER;
   case ELO;
   case FORTBRASIL;
   case GRANDCARD;
   case HIPER;
   case HIPERCARD;
   case JCB;
   case MAIS;
   case MASTERCARD;
   case PERSONALCARD;
   case PLENOCARD;
   case POLICARD;
   case SOROCRED;
   case UPBRASIL;
   case VALECARD;
   case VERDECARD;
   case VISA;
}

class PaymentMethod
{
    public function __construct(public string $type, public array|null $brands = null){}

    public static function createCreditCard(array $brands = null)
    {
        return new PaymentMethod("credit_card", $brands);
    }

    public static function createDebitCard(array $brands = null)
    {
        return new PaymentMethod("debit_card", $brands);
    }

    public static function createPix()
    {
        return new PaymentMethod("pix");
    }

    public static function createBoleto()
    {
        return new PaymentMethod("boleto");
    }

    public function __toString()
    {
        return json_encode($this);
    }
}
