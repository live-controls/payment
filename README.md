# Payment
 ![Release Version](https://img.shields.io/github/v/release/live-controls/payment)
 ![Packagist Version](https://img.shields.io/packagist/v/live-controls/payment?color=%23007500)

 Helper classes and objects to create online checkouts for IUGU and PagSeguro. 
 
 **No warranty for any unwanted sideeffects!**

## Requirements
- PHP 8.0+
- GuzzleHttp\Guzzle


## Translations
None


## Installation

1. Install Payment package
```ps
composer require live-controls/payment
```
2. Add PAGSEGURO_EMAIL_DEBUG and PAGSEGURO_TOKEN_DEBUG
3. Add PAGSEGURO_EMAIL and PAGSEGURO_TOKEN

### Content
- Transparent Checkout for IUGU (incl. PaymentItem and PaymentSender classes)
- Redirect Checkout for PagSeguro (incl. PaymentItem, ShippingInformation, PaymentReceiver and PaymentSender classes)


## Usage
### IUGU
**Warning: No longer updated, but should probably work**

### PagSeguro
1) Create Token for testing and for production on PagSeguro (If you don't know how, ask PagSeguro)

