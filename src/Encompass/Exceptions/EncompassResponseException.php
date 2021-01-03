<?php
namespace Encompass\Exceptions;

use Encompass\EncompassResponse;

class EncompassResponseException extends EncompassSDKException
{
    private $response;
    private $responseData;

    public function __construct(EncompassResponse $response, EncompassSDKException $previousException = null)
    {
        $this->response = $response;
        $this->responseData = $response->getDecodedBody();

        $errorMessage = $this->get('message', 'Unknown error from Encompass.');
        $errorCode = $this->get('status', -1);

        if (empty($this->responseData)) {
            $errorCode = $response->getHttpCode();
            $errorMessage = $response->getPhrase();
        }

        parent::__construct($errorMessage, $errorCode, $previousException);
    }

    private function get($key, $default = null)
    {
        if (isset($this->responseData['error'][$key])) {
            return $this->responseData['error'][$key];
        }
        return $default;
    }

    public static function create(EncompassResponse $response)
    {
        $data = $response->getDecodedBody();
        $code = isset($data['error']['status']) ? $data['error']['status'] : -1;
        $message = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown error from Encompass.';

        if (empty($data)) {
            $code = $response->getHttpCode();
            $message = $response->getPhrase();
        }

        if (isset($data['error']['error_subcode'])) {
           //sub codes
        }

        switch ($code) {
            case 404:
                return new static($response, new EncompassNotFoundResourceException($message, $code));
            case 422:
                return new static($response, new EncompassValidationException($message, $code));
        }

        //authentication error
        if (isset($data['error']['type']) && $data['error']['type'] === 'CredentialsException') {
            return new static($response, new EncompassAuthenticationException($message, $code));
        }

        // All others
        return new static($response, new EncompassSDKException($message, $code));
    }
}
