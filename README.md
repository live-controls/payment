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
4. Publish configuration file
```ps
php artisan vendor:publish --tag="livecontrols.payment.config"
```


## Configuration
- 'logging' => If set to true, the library will log the requests and responses even in production mode.
- 'debug' => Defaults to the applications debug mode. This way you can set the library to debug mode, without setting the whole application to it.
- 'pagseguro_email_debug' => The email from your pagseguro account to be used in debug/sandbox mode.
- 'pagseguro_token_debug' => The token from your pagseguro account to be used in debug/sandbox mode. (Needs to be your sandbox token, else an exception will be thrown)
- 'pagseguro_email' => The email from your pagseguro account to be used in production mode.
- 'pagseguro_token' => The token from your pagseguro account to be used in production mode. (Needs to be your production token, else an exception will be thrown)

### Content
- Transparent Checkout for IUGU (incl. PaymentItem and PaymentSender classes)
- Redirect Checkout for PagSeguro (incl. PaymentItem, ShippingInformation, ~PaymentReceiver~ and PaymentSender classes)


## Usage
### IUGU
**Warning: No longer updated, but should probably work**

### PagSeguro
1) Create Token for testing and for production on PagSeguro (If you don't know how, ask PagSeguro)

