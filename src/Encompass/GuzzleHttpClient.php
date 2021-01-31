<?php
namespace Encompass;

use Encompass\Exceptions\EncompassResponseException;
use Encompass\Exceptions\MissingEnvironmentVariablesException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class GuzzleHttpClient
{
    /**
     * @var \GuzzleHttp\Client The Guzzle client.
     */
    protected $guzzleClient;

    const ENCOMPASS_USER = 'ENCOMPASS_USER';

    const ENCOMPASS_PASSWORD = 'ENCOMPASS_PASSWORD';

    const ENCOMPASS_DOMAIN = 'ENCOMPASS_DOMAIN';

    /**
     * GuzzleHttpClient constructor.
     * @param Client|null $guzzleClient
     */
    public function __construct(Client $guzzleClient = null, $user)
    {
        $this->user =  $user;
        $this->guzzleClient = $guzzleClient ?: new Client();
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        if (empty(config('encompass.domain'))) {
            throw new MissingEnvironmentVariablesException('Encompass Domain is require in Encompass config file.');
        }
        return  config('encompass.domain') ;
    }

    /**
     * @return mixed
     * @throws MissingEnvironmentVariablesException
     */
    public function getToken()
    {
        if (! Cache::has('token_' . $this->user->id)) {
            throw new MissingEnvironmentVariablesException('Encompass Token is require in request.');
        }
        return Cache::get('token_' . $this->user->id);
    }

    /**
     * @return Client
     */
    public function getGuzzleClient()
    {
        return $this->guzzleClient;
    }

    /**
     * @param EncompassRequest $request
     * @return array
     */
    public function prepareRequestMessage(EncompassRequest $request)
    {
        $request->setHeaders([
            'Authorization' => "Bearer {$this->getToken()}"
        ]);

        return [
            $request->getEndpoint(),
            $request->getMethod(),
            $request->getHeaders(),
            $request->getParams()
        ];
    }

    /**
     * @param EncompassRequest $request
     * @return EncompassResponse
     * @throws Exceptions\EncompassResponseException
     * @throws GuzzleException
     */
    public function sendRequest(EncompassRequest $request)
    {
        list($url, $method, $headers, $parameters) = $this->prepareRequestMessage($request);

        try {
            $rawResponse = $this->guzzleClient->request($method, $url, array_merge($parameters, $headers));
        } catch (RequestException $e) {
            $rawResponse = $e->getResponse();
        }

        $returnResponse = new EncompassResponse(
            $request,
            $rawResponse->getBody(),
            $rawResponse->getStatusCode(),
            $rawResponse->getReasonPhrase(),
            $rawResponse->getHeaders()
        );
        
        if ($returnResponse->isError()) {
            throw $returnResponse->getThrownException();
        }

        return $returnResponse;
    }

}
