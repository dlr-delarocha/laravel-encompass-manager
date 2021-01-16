<?php
namespace Encompass;

use App\Models\EncompassAccount;
use Encompass\Client\HttpClient;
use Encompass\Exceptions\EncompassAuthenticationException;
use Encompass\Exceptions\MissingEnvironmentVariablesException;
use http\Client;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Crypt;

class AuthRequest extends HttpClient
{
    public $method;

    protected $client;

    protected $user;

    static $format_user_name = '%s@encompass:%s';

    /**
     * AuthRequest constructor.
     * @param null $method
     * @param $user
     * @throws \Exception
     */
    public function __construct($method = null, $user)
    {
        $this->setMethod($method);
        $this->user = $user;
        $this->client = $this->createHttpClient($user);
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
        $rawResponse = $this->login($this->user);

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
        return sprintf(self::$format_user_name, $account->user,  $account->user_client_id);
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
     * @todo must be changed for a User Model request
     * @return mixed
     */
    public function getUser()
    {
        if (empty(config('encompass.user')) && is_null($this->user)) {
            throw new MissingEnvironmentVariablesException('Encompass User is require in Encompass config file.');
        }

        $account = $this->user->encompassAccount;
        if (empty ($account)) {
            throw new EncompassAuthenticationException('EncompassAccount model empty');
        }

        throw new EncompassAuthenticationException('Afuera');


        return $this->buildNameByAuthenticationType($account);
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        if (empty(config('encompass.password')) && is_null($this->user->encompassAccount)) {
            throw new MissingEnvironmentVariablesException('Encompass password is require.');
        }

        $password = Crypt::decryptString($this->user->encompassAccount->password);

        if (! $password) {
            throw new AuthenticationException('Encompass Password is require');
        }

        return  $password;
    }

    private function getClientId()
    {
        if (empty(config('encompass.client_id')) && is_null($this->user->encompassAccount)) {
            throw new MissingEnvironmentVariablesException('Encompass Client_id is require.');
        }

        $clientId = $this->user->encompassAccount->client_id;
        if (! $clientId) {
            throw new AuthenticationException('Encompass Client_secret is require');
        }
        return  $clientId;
    }

    private function getSecret()
    {
        if (empty(config('encompass.client_id')) && is_null($this->user->encompassAccount)) {
            throw new MissingEnvironmentVariablesException('Encompass Client_secret is require.');
        }

        $secret = $this->user->encompassAccount->client_secret;
        if (! $secret) {
            throw new AuthenticationException('Encompass Client_secret is require');
        }

        return $secret;
    }

    /**
     * @param null $user
     * @return \Psr\Http\Message\ResponseInterface
     * @throws EncompassAuthenticationException
     * @throws MissingEnvironmentVariablesException
     */
    private function login()
    {
        $request = $this->client->getGuzzleClient();
        return $request->request('POST', config('encompass.domain') . $this->getEndpoint(),
            [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' =>  $this->getClientId(),
                    'client_secret' =>  $this->getSecret(),
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
