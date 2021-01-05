<?php
namespace Encompass;

use Encompass\Client\HttpClient;
use Encompass\Exceptions\MissingEnvironmentVariablesException;
use http\Client;
use Illuminate\Support\Facades\Cache;

class AuthRequest extends HttpClient
{
    public $method;
    protected $client;

    /**
     * AuthRequest constructor.
     * @param null $method
     * @param null $endpoint
     * @throws \Exception
     * @return AuthRequest
     */
    public function __construct($method = null, $endpoint = null)
    {
        $this->setMethod($method);
        $this->client = $this->createHttpClient();
        return $this;

    }

    /**
     * @return string
     */
    protected function getEndpoint()
    {
        return '/oauth2/v1/token';
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
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
     * @todo must be changed for a User Model request
     * @return mixed
     */
    public function getUser()
    {
        if (empty(config('encompass.user'))) {
            throw new MissingEnvironmentVariablesException('Encompass User is require in Encompass config file.');
        }
        $format = '%s@encompass:%s';
        return sprintf($format, config('encompass.user'),  config('encompass.user_client_id'));
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        if (empty(config('encompass.password'))) {
            throw new MissingEnvironmentVariablesException('Encompass Password is require in Encompass config file.');
        }
        return  config('encompass.password') ;
    }
    
    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @throws MissingEnvironmentVariablesException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function login()
    {
        $request = $this->client->getGuzzleClient();
        return $request->request('POST', config('encompass.domain') . $this->getEndpoint(),
            [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => config('encompass.client_id'),
                    'client_secret' => config('encompass.client_secret'),
                    'username' => $this->getUser(),
                    'password' => $this->getPassword()
                ]
            ]
        );
    }
    
    /**
     * @param EncompassResponse $response
     * @return bool
     */
    public function saveTokenFromResponse(EncompassResponse $response)
    {
        $data = $response->getDecodedBody();
        return array_key_exists('access_token', $data) ? $data['access_token'] : null;
    }
}
