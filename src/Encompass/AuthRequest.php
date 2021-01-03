<?php
namespace Encompass;

use Illuminate\Support\Facades\Cache;

class AuthRequest
{
    public $method;
    public $endpoint;

    /**
     * AuthRequest constructor.
     * @param null $method
     * @param null $endpoint
     */
    public function __construct($method = null, $endpoint = null)
    {
        $this->setMethod($method);
        $this->setEndpoint($endpoint);

    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getEndpoint()
    {
        return $this->endpoint;
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
     * @param $endpoint
     * @return $this
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * @param EncompassResponse $response
     * @return bool
     */
    public function saveTokenFromResponse(EncompassResponse $response)
    {
        $data = $response->getDecodedBody();
        if (array_key_exists('access_token', $data['token'])) {
            return $data['token']['access_token'];
        }
    }
}
