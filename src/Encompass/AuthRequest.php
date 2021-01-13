<?php
namespace Encompass;

use App\Models\EncompassAccount;
use Encompass\Client\HttpClient;
use Encompass\Exceptions\EncompassAuthenticationException;
use Encompass\Exceptions\MissingEnvironmentVariablesException;
use http\Client;
use Illuminate\Support\Facades\Cache;

class AuthRequest extends HttpClient
{
    public $method;

    protected $client;

    static $format_user_name = '%s@encompass:%s';

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
    public function refreshToken(AuthRequest $request, $user = null)
    {
        try {
            $rawResponse = $this->login($user);
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
    public function getUser($user = null)
    {
        if (empty(config('encompass.user')) && is_null($user)) {
            throw new MissingEnvironmentVariablesException('Encompass User is require in Encompass config file.');
        }

        if (! is_null($user)) {
            $account = $user->encompassAccount;
        }
        return $this->buildNameByAuthenticationType($account);
    }

    /**
     * @param null $account
     * @return string
     * @throws EncompassAuthenticationException
     */
    public function buildNameByAuthenticationType($account = null)
    {
        if ($this->isAuthenticationByModel() && empty($account)) {
            throw new EncompassAuthenticationException('Encompass Account is required');
        }

        return $this->isAuthenticationByModel() ? $this->buildByModel($account) : $this->buildByDefault($account);
    }

    /**
     * @return bool
     */
    private function isAuthenticationByModel()
    {
        return config('encompass.auth.type') === 'model';
    }

    /**
     * @param EncompassAccount $account
     * @return string
     */
    private function buildByModel(EncompassAccount $account)
    {
        return sprintf(self::$format_user_name, $account->user,  $account->client_id);
    }

    /**
     * @param EncompassAccount $account
     * @return string
     */
    private function buildByDefault(EncompassAccount $account)
    {
        return sprintf(self::$format_user_name, config('encompass.user'),  config('encompass.user_client_id'));
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
    private function login($user = null)
    {
        $request = $this->client->getGuzzleClient();
        return $request->request('POST', config('encompass.domain') . $this->getEndpoint(),
            [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => config('encompass.client_id'),
                    'client_secret' => config('encompass.client_secret'),
                    'username' => $this->getUser($user),
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
