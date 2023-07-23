# Payment
 ![Release Version](https://img.shields.io/github/v/release/live-controls/payment)
 ![Packagist Version](https://img.shields.io/packagist/v/live-controls/payment?color=%23007500)

 Helper classes and objects to create online checkouts for IUGU and PagSeguro. 
 **No warranty for any unwanted sideeffects!**

## Requirements
- PHP 8.0+


## Translations
None


## Installation

1. Install Payment package
```ps
composer require live-controls/payment
```


### Content
- Transparent Checkout for IUGU (incl. PaymentItem and PaymentSender classes)
- Redirect Checkout for PagSeguro (incl. PaymentItem, ShippingInformation, PaymentReceiver and PaymentSender classes)


## Usage
Todo
* prefix = The prefix is optional, but needed if you add more than one AutoCep component on a single page. It will be prefix_road etc. afterwards
* titlesuffix = The suffix of the title, usually you'd set a * if they're required or such.
* oldmodel = This is optional, if set, it will take the cep, street, bairro, uf and city of the model
* required = If set to true it will set all inputs to "required"

**Important: In case you want to use more than one AutoCEP component, don't forget to add a "prefix" so the informations won't be overwritten!**
