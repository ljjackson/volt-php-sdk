<?php

namespace LJJackson\Volt\Services;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use LJJackson\Volt\Entities\AccessToken;
use LJJackson\Volt\Entities\PaymentRequest;
use LJJackson\Volt\Exceptions\PaymentValidationException;

class PaymentService extends BaseService
{

    /**
     * @param AccessToken $token
     * @param array $data
     * @return PaymentRequest
     * @throws GuzzleException|PaymentValidationException
     * @link https://docs.volt.io/docs/first_payment/request_payment
     */
    public function requestPayment(AccessToken $token, array $data): PaymentRequest
    {
        try {
            $response = $this->post('payments', [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . $token->getAccessToken(),
                ],
                RequestOptions::JSON => $data,
            ]);
        } catch (RequestException $exception) {
            $response = json_decode($exception->getResponse()->getBody()->getContents(), true);

            throw new PaymentValidationException($response['exception']['errorList'], $exception->getResponse()->getStatusCode());
        }

        $request = new PaymentRequest(json_decode($response->getBody()->getContents(), true));

        if ($this->apiConfig->isSandbox()) {
            return $request->setSandbox();
        }

        return $request;
    }
}