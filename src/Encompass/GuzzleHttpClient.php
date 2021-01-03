<?php
namespace Encompass;

use Encompass\Exceptions\EncompassResponseException;
use Encompass\Exceptions\MissingEnvironmentVariablesException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;

class GuzzleHttpClient
{
    /**
     * @var \GuzzleHttp\Client The Guzzle client.
     */
    protected $guzzleClient;

    const Encompass_USER = 'Encompass_USER';

    const Encompass_PASSWORD = 'Encompass_PASSWORD';

    const Encompass_DOMAIN = 'Encompass_DOMAIN';

    /**
     * EncompassGuzzleHttpClient constructor.
     * @param \GuzzleHttp\Client|null $guzzleClient
     */
    public function __construct(Client $guzzleClient = null)
    {
        $this->guzzleClient = $guzzleClient ?: new Client();
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        if (empty(config('Encompass.domain'))) {
            throw new MissingEnvironmentVariablesException('Encompass Domain is require in Encompass config file.');
        }
        return  config('Encompass.domain') ;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        if (empty(config('Encompass.user'))) {
            throw new MissingEnvironmentVariablesException('Encompass User is require in Encompass config file.');
        }
        return  config('Encompass.user') ;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        if (empty(config('Encompass.password'))) {
            throw new MissingEnvironmentVariablesException('Encompass Password is require in Encompass config file.');
        }
        return  config('Encompass.password') ;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        if (!Cache::has('access_token')) {
            throw new MissingEnvironmentVariablesException('Encompass Token is require in request.');
        }
        return Cache::get('access_token');
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
        $url = $this->getBaseUrl(). $request->getEndpoint();
        $request->setHeaders([
            'Authorization' => "Bearer {$this->getToken()}"
        ]);

        return [
            $url,
            $request->getMethod(),
            $request->getHeaders()
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
        list($url, $method, $headers) = $this->prepareRequestMessage($request);

        try {
            $rawResponse = $this->guzzleClient->request($method, $url, $headers);
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

    /**
     * @param AuthRequest $request
     * @throws Exceptions\EncompassResponseException
     * @throws GuzzleException
     */
    public function refreshToken(AuthRequest $request)
    {
        try {
            $rawResponse = $this->login();
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
        
        return $request->saveTokenFromResponse($returnResponse);
    }

    /**
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    private function login()
    {
        return $this->getGuzzleClient()->request(
            'POST',
            $this->getBaseUrl() . '/login',
            [
                'form_params' => [
                    'email' => $this->getUser(),
                    'password' => $this->getPassword()
                ]
            ]
        );
    }
}
