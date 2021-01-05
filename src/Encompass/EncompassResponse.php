<?php
namespace Encompass;

use Encompass\Exceptions\EncompassResponseException;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Closure;

class EncompassResponse
{
    /**
     * @var int The HTTP status code response from Encompass.
     */
    protected $httpStatusCode;

    /**
     * @var array The headers returned from Encompass.
     */
    protected $headers;

    /**
     * @var string The raw body of the response from Encompass.
     */
    protected $body;

    /**
     * @var array The decoded body of the Encompass response.
     */
    protected $decodedBody = [];

    /**
     * @var AuthRequest|EncompassRequest
     */
    protected $request;

    /**
     * @var EncompassResponseException
     */
    protected $thrownException;

    /**
     * @var string
     */
    protected $phrase;

    /**
     * EncompassResponse constructor.
     *
     * @param AuthRequest|EncompassRequest  $request
     * @param null $body
     * @param null $httpStatusCode
     * @param string $phrase
     * @param array $headers
     */
    public function __construct(
        $request,
        $body = null,
        $httpStatusCode = null,
        $phrase = '',
        array $headers = []
    ) {
        $this->request = $request;
        $this->body = $body;
        $this->httpStatusCode = $httpStatusCode;
        $this->phrase = $phrase;
        $this->headers = $headers;

        $this->decodeBody();
    }

    /**
     * Decode body if there are a error then make an exception
     */
    private function decodeBody()
    {
        $this->decodedBody = json_decode($this->body, true);
                        
        if ($this->isError() || is_null($this->decodedBody)) {
            $this->makeException();
        }
    }

    /**
     * Returns true if Encompass returned an error message.
     *
     * @return boolean
     */
    public function isError()
    {
        return isset($this->decodedBody['error']) || is_null($this->decodedBody);
    }

    /**
     * Instantiates an exception to be thrown later.
     */
    private function makeException()
    {
        $this->thrownException = EncompassResponseException::create($this);
    }

    /**
     * @return array
     */
    public function getDecodedBody()
    {
        return $this->decodedBody;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return int|null
     */
    public function getHttpCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * @return string
     */
    public function getPhrase()
    {
        return $this->phrase;
    }

    /**
     * @return EncompassRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return EncompassResponseException
     */
    public function getThrownException()
    {
        return $this->thrownException;
    }

    /**
     * develop mode
     * @param Closure $callback
     * @return $this
     */
    public function map(Closure $callback)
    {
        return new static(
            $this->request,
            array_map($callback, $this->getItems(), array_keys($this->getItems()))
        );
    }

    /**
     * develop mode
     * @return array|mixed
     */
    public function toXML()
    {
        $array = $this->getDecodedBody();
        return $this->arrayToXML($array, new SimpleXMLElement('<LoanResponse/>'))->asXML();
    }

    /**
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     */
    function arrayToXML(array $array, SimpleXMLElement $xml)
    {
        foreach ($array as $key => $value) {
            if (is_array($value) ) {
              
                if (is_numeric($key)) {
                    $this->arrayToXML($value, $xml->addChild(Str::singular($xml->getName())));
                } else {
                    $this->arrayToXML($value, $xml->addChild($key));
                }            
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }         
 
        }
        return $xml;
    }
}
