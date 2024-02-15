<?php

return [
    'logging' => true,
    'debug' => config('app.debug', false),
    'pagseguro_email_debug' => env('PAGSEGURO_EMAIL_DEBUG',null),
    'pagseguro_token_debug' => env('PAGSEGURO_TOKEN_DEBUG', null),
    'pagseguro_email' => env('PAGSEGURO_EMAIL', null),
    'pagseguro_token' => env('PAGSEGURO_TOKEN', null)
];