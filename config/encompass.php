<?php

return array(
    'client_id' => env('CLIENT_ID'),
    'client_secret' => env('CLIENT_SECRET'),
    'domain' => env('ENCOMPASS_DOMAIN'),
    'user' => env('ENCOMPASS_USER_ID'),
    'user_client_id' => env('ENCOMPASS_USER_ID'),
    'password' => env('ENCOMPASS_PASSWORD'),

    //authentication
    //model, local
    'auth' => [
        'type' => env('ENCOMPASS_AUTH_TYPE', 'model'),
        'model' => 'encompassAccount'
    ]

);

