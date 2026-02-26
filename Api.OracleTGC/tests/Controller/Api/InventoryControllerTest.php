<?php

namespace App\Tests\Controller\Api;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class InventoryControllerTest extends ApiTestCase
{
    public function testShowWithoutTokenReturns401(): void
    {
        $response = $this->get('/api/inventory');
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }
}
