<?php

return array(
    'client_id' => env('CLIENT_ID'),
    'client_secret' => env('CLIENT_SECRET'),
    'domain' => env('ENCOMPASS_DOMAIN'),
    'user' => env('ENCOMPASS_USER_ID', 'admin'),
    'user_client_id' => env('ENCOMPASS_USER_ID', 'BE11137124'),
    'password' => env('ENCOMPASS_PASSWORD', 'Ginnie101@'),

    //authentication
    //model, local
    'auth' => [
        'type' => env('ENCOMPASS_AUTH_TYPE', 'model'),
        'model' => 'encompassAccount'
    ]

);

