# Laravel-Encompass-sdk
This repository contains the open source PHP SDK that allows you to access the Encompass Platform from your Lumen/Laravel app.

## Lumen Configuration

### Install with composer

```sh
composer require delarocha/laravel-Encompass-sdk
```

#### Create Encompass.php file in config directory.
```
app
config
  Encompass.php
```


#### Add the following configuration array.    
```
return array(
    'user' => env('Encompass_USER'),
    'password' => env('Encompass_PASSWORD'),
    'domain' => env('Encompass_DOMAIN'),
);
```

#### Include Encompass config file file in boostrap/app   
```
$app->configure('Encompass');
```


#### Example
```
use Encompass\Encompass;

    $Encompass = new Encompass;
    $segments = $Encompass->getService()->get('/segments')->getItems();
```

License
----

MIT

