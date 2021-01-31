<?php
namespace Encompass;

use Encompass\Client\HttpClient;
use Encompass\Exceptions\EncompassSDKException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ApiRequest extends HttpClient
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
    public function __construct($user)
    {
        $this->client = $this->createHttpClient($user);
        return $this;
    }

    /**
     * @return EncompassGuzzleHttpClient
     */
    protected function getClient()
    {
        return $this->client;
    }

    /**
     * @param $endpoint
     * @param string|null $accessToken
     * @param array $params
     * @return EncompassResponse
     * @throws Exceptions\EncompassResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($endpoint, $params = [])
    {
        try {
            return $this->sendRequest(
                'GET',
                config('encompass.domain') . $endpoint,
                $params
            );
        } catch (\Exception $e) {
           throw new EncompassSDKException($e);
        }
    }

    /**
     * @param $endpoint
     * @param array $params
     * @return EncompassResponse
     * @throws EncompassSDKException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($endpoint, $params = [], $headers = [])
    {
        try {
            return $this->sendRequest(
                'POST',
                config('encompass.domain') . $endpoint,
                $params,
                $headers
            );
        } catch (\Exception $e) {

            throw new EncompassSDKException($e);
        }
    }

    /**
     * @param $endpoint
     * @param array $params
     * @return EncompassResponse
     * @throws EncompassSDKException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function put($endpoint, $params = [], $headers = [])
    {
        try {
            return $this->sendRequest(
                'PUT',
                Str::contains($endpoint, 'https') ? $endpoint : config('encompass.domain') . $endpoint,
                $params,
                $headers
            );
        } catch (\Exception $e) {

            throw new EncompassSDKException($e);
        }
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
    private function sendRequest($method, $endpoint, array $params = [], array $headers = [])
    {
        $request = $this->request($method, $endpoint, $params, $headers);

        return $this->lastResponse = $this->client->sendRequest($request);
    }

    /**
     * @param $method
     * @param $endpoint
     * @param array $params
     * @param null $accessToken
     * @return EncompassRequest
     */
    private function request($method, $endpoint, array $params = [], array $headers = [])
    {
        return new EncompassRequest(
            $method,
            $endpoint,
            $params,
            $headers
        );
    }


}
