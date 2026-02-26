<?php

namespace App\Tests\Controller\Api;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CurrencyControllerTest extends ApiTestCase
{
    public function testGetRatesWithoutTokenReturns401(): void
    {
        $response = $this->get('/api/currency/rates');
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testConvertWithoutTokenReturns401(): void
    {
        $response = $this->get('/api/currency/convert?usd=10');
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }
}
