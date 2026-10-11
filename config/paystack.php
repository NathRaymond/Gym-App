<?php

return [
    'mode'           => env('PAYSTACK_MODE', 'test'),
    'public_key'     => env('PAYSTACK_PUBLIC_KEY'),
    'secret_key'     => env('PAYSTACK_SECRET_KEY'),
    'payment_url'    => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
    'merchant_email' => env('PAYSTACK_MERCHANT_EMAIL'),
    'callback_url'   => env('PAYSTACK_CALLBACK_URL'),
    'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
    'currency'       => 'NGN',
    'ca_bundle'      => env('PAYSTACK_CA_BUNDLE', 'C:\wamp64\bin\php\php8.5.0\extras\ssl\cacert.pem'),
];