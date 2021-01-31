<?php
namespace Encompass;

use Illuminate\Support\Facades\Cache;

class EncompassRequest
{
    private $endpoint;
    private $params = array();
    private $headers = array();
    private $method;
    private $token = null;

    /**
     * EncompassRequest constructor.
     * @param null|string $method
     * @param null|string $endpoint
     * @param null|string $accessToken
     * @param array $params
     */
    public function __construct($method = null, $endpoint = null, array $params = [], array $headers = [])
    {
        $this->setMethod($method);
        $this->setEndpoint($endpoint);
        $this->setParams($params);
        $this->setHeaders($headers);
    }
    
    /**
     * @param $method
     * @return $this
     */
    private function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param $endpoint
     * @return $this
     */
    private function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * @param $params
     * @return $this
     */
    private function setParams($params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }
    
    /**
     * @return array
     */
    public function getHeaders()
    {
        return ['headers' => $this->headers];
    }
    

    /**
     * Set the headers for this request.
     *
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    /**
     * @return mixed
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }
}
