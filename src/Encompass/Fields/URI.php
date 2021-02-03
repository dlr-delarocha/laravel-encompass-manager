<?php

namespace Encompass\Fields;

class URI
{
    const URIs = [
        'import' => '/encompass/v1/loans?loanFolder=%s&view=entity',
        'loans' => '/encompass/v1/loans/',
        'create-folder' => '/encompass/v1/loans/%s/attachments/url?view=id',
        'xmlJson'   => '/encompass/v3/converter/loans?mediaType=mismo'
    ];

    /**
     * @param $attribute
     * @param mixed ...$params
     * @return string
     * @throws \Exception
     */
    public static function uri($attribute, ...$params)
    {
        if(! ($uri = self::URIs[$attribute])) {
            throw new \Exception('Invalid attribute');
        }
        
        return $params ? sprintf($uri, implode(',' , $params)) : $uri;
    }

}