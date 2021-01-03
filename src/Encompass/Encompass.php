<?php
namespace Encompass;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class Encompass
{
    /**
     * @var EncompassGuzzleHttpClient
     */
    protected $client;
    /**
     * @var
     */
    protected $lastResponse;

    /**
     * $client @see Client
     * Encompass constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->client = $this->createHttpClient();
        return $this;
    }

    /**
     * @return $this
     * @throws Exceptions\EncompassResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getService($time = 86400)
    {
        Cache::remember('access_token', $time, function () {
            return $this->login();
        });
        return $this;
    }

    /**
     * @return EncompassGuzzleHttpClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @throws Exceptions\EncompassResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function login()
    {
        $request = $this->loginRequest(
            'POST',
            '/login'
        );

        return $this->client->refreshToken($request);
    }

    /**
     * @return EncompassGuzzleHttpClient
     * @throws \Exception
     */
    private function createHttpClient()
    {
        if (!class_exists('GuzzleHttp\Client')) {
            throw new \Exception('The Guzzle HTTP client must be included in order to use the "guzzle" handler.');
        }

        return new EncompassGuzzleHttpClient(new Client());
    }

    /**
     * @param $endpoint
     * @param string|null $accessToken
     * @param array $params
     * @return EncompassResponse
     * @throws Exceptions\EncompassResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($endpoint, $accessToken = null, $params = [])
    {
        return $this->sendRequest(
            'GET',
            $endpoint,
            $accessToken,
            $params
        );
    }

    /**
     * @param $method
     * @param $endpoint
     * @param null $accessToken
     * @param array $params
     * @return EncompassResponse
     * @throws Exceptions\EncompassResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendRequest($method, $endpoint, $accessToken = null, array $params = [])
    {
        $request = $this->request($method, $endpoint, $accessToken, $params);
        return $this->lastResponse = $this->client->sendRequest($request);
    }

    /**
     * @param $method
     * @param $endpoint
     * @param array $params
     * @param null $accessToken
     * @return EncompassRequest
     */
    private function request($method, $endpoint, $accessToken = null, array $params = [])
    {
        return new EncompassRequest(
            $method,
            $endpoint,
            $accessToken,
            $params
        );
    }

    /**
     * @param $method
     * @param $endpoint
     * @return AuthRequest
     */
    private function loginRequest($method, $endpoint)
    {
        return new AuthRequest(
            $method,
            $endpoint
        );
    }
}
