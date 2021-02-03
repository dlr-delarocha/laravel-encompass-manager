<?php

namespace Encompass\Objects;

use Encompass\ApiRequest;
use Encompass\Fields\LoanFields;
use Encompass\Fields\URI;
use Illuminate\Support\Facades\Storage;

class Loan
{
    protected $api;
    protected $responseXml;

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
        // return '/encompass/v1/loans/';
        return '/encompass/v3/loans/';
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
            URI::uri('loans') . $id,
            [
                'query' => [
                    'entities' => $this->defaultParameters(),
                    'metadata' => false
                ]
            ]
        );
    }

    /**
     * @param $applications
     * @param $folder
     * @return \Encompass\EncompassResponse
     */
    public function createLoan($applications, $folder)
    {
        return $this->newImport($applications, $folder);
    }



    protected function newImport($applications, string $folder)
    {
            // dd(json_decode($applications));
        return $this->api->post(
            URI::uri('import', $folder), [
                'json' => $applications
            ],
            ['Content-Type' => 'application/json']
        );
    }   

    public function createLoanFolder($id, ...$params)
    {
        return $this->api->post(
            URI::uri('create-folder', $id), [
                'json' => [
                    'title' => $params[0],
                    'fileWithExtension' => $id . '.' . $params[0] . '.pdf',
                    'createReason' => 1
                ]
            ],
            ['Content-Type' => 'application/json']
        );
    }

    public function attachmentLoanRequest(string $allowedUrl, $kycId, $content)
    {
        return $this->api->put($allowedUrl, [
            'multipart' => array(
                [
                    'name' => $kycId . '.pdf',
                    'contents' => $content
                ]
            ),
        ], ['Content-Type' => 'multipart/form-data']);
    }

    public function converXmltoJson($xml)
    {
        $myArray = $this->api->post(
            URI::uri('xmlJson'),
            [
                'body' => $xml
            ],
            ['Content-Type' => 'application/vnd.elliemae.mismo34+xml']
        )->toCollection();

        $myArray = $this->array_filter_recursive($myArray->toArray());

        return $this->newImport($myArray,'Zense_Import');

    }

    private function array_filter_recursive($input){
        foreach ($input as &$value){
            if (is_array($value)){
                $value = $this->array_filter_recursive($value);
            }
        }
        return array_filter($input);
    } 

}