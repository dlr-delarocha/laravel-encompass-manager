<?php

namespace Encompass;

use App\Lender;
use Encompass\Client\HttpClient;
use Encompass\Objects\Loan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Encompass extends HttpClient
{
    protected $client;

    protected $user;

    /**
     * @todo this argument model must be included in the package
     * Encompass constructor.
     * @throws \Exception
     */
    public function __construct(Model $user = null)
    {
        $this->client = $this->createHttpClient();
        $this->user = $user;
        return $this;
    }

    /**
     * @return $this
     * @throws Exceptions\EncompassResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getService()
    {
        Cache::remember('access_token', now()->addMinutes(14), function () {
            return $this->login();
        });
        return $this;
    }

    /**
     * @throws Exceptions\EncompassResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function login()
    {
        $request = $this->loginRequest(
            'POST',
            '/login'
        );

        return $request->refreshToken($request, $this->user);
    }

    /**
     * @param $method
     * @param $endpoint
     * @return AuthRequest
     * @throws \Exception
     */
    private function loginRequest($method, $endpoint)
    {
        return new AuthRequest(
            $method,
            $endpoint
        );
    }

    /**
     * @todo must be changed for a factory
     * @return Loan
     */
    public static function loan()
    {
        return new Loan();
    }


}