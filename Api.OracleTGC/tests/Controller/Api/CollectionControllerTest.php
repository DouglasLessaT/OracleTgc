<?php

namespace App\Tests\Controller\Api;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CollectionControllerTest extends ApiTestCase
{
    public function testListWithoutTokenReturns401(): void
    {
        $response = $this->get('/api/collections');
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testCreateWithoutTokenReturns401(): void
    {
        $response = $this->postJson('/api/collections', ['name' => 'Test']);
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }
}
