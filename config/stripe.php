<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe Publishable Key
    |--------------------------------------------------------------------------
    | Used on the front-end (Stripe.js) to tokenise card data.
    | Prefix: pk_test_... (test) | pk_live_... (production)
    */
    'key' => env('STRIPE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Stripe Secret Key
    |--------------------------------------------------------------------------
    | Used server-side to call the Stripe API.
    | Prefix: sk_test_... (test) | sk_live_... (production)
    | If this contains the word "mock" the service falls back to local simulation.
    */
    'secret' => env('STRIPE_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhook Signing Secret
    |--------------------------------------------------------------------------
    | Used to verify that incoming webhook events were sent by Stripe.
    | Obtain from: Dashboard → Developers → Webhooks → your endpoint → Signing secret
    | Or via the Stripe CLI: stripe listen --forward-to ...
    | Prefix: whsec_...
    */
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    | ISO 4217 currency code (lowercase). Stripe requires lowercase.
    | e.g. usd, inr, eur, gbp
    */
    'currency' => env('STRIPE_CURRENCY', 'usd'),

    /*
    |--------------------------------------------------------------------------
    | Stripe API Version
    |--------------------------------------------------------------------------
    | Pin a specific API version for stability. Leave null to use Stripe's
    | default version for your account.
    */
    'api_version' => env('STRIPE_API_VERSION', null),

];
