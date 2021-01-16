<?php

namespace Encompass\Objects;

use Encompass\ApiRequest;
use Encompass\Fields\LoanFields;

class Loan
{
    protected $api;

    /**
     * Loan constructor.
     * @param $api
     */
    public function __construct($user)
    {
        $this->api = new ApiRequest($user);
    }

    private function getEndpoint()
    {
        return '/encompass/v1/loans/';
    }

    public function getSelf($id)
    {
        return $this->getLoanById($id);
    }

    protected function defaultParameters()
    {
        return implode(',',  LoanFields::getFields());
    }

    /**
     * @param $id
     * @return \Encompass\EncompassResponse
     * @throws \Encompass\Exceptions\EncompassResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getLoanById($id)
    {
        return $this->api->get(
            $this->getEndpoint() . $id,
            [
                'query' => [
                    'entities' => $this->defaultParameters(),
                    'metadata' => false
                ]
            ]
        );
    }
}
