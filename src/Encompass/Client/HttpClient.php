<?php

namespace Encompass\Client;

use Encompass\GuzzleHttpClient;
use GuzzleHttp\Client;

class HttpClient
{
    /**
     * @return EncompassGuzzleHttpClient
     * @throws \Exception
     */
    protected function createHttpClient()
    {
        if (!class_exists('GuzzleHttp\Client')) {
            throw new \Exception('The Guzzle HTTP client must be included in order to use the "guzzle" handler.');
        }

        return new GuzzleHttpClient(new Client());
    }

}