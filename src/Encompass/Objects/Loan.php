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
    public function __construct()
    {
        $this->api = new ApiRequest();
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

    private function getLoanById($id)
    {
        $request = new ApiRequest();
        return $request->get(
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
