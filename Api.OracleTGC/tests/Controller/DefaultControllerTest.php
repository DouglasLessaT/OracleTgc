<?php

namespace App\Tests\Controller;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends ApiTestCase
{
    public function testGetRootReturnsApiInfo(): void
    {
        $response = $this->get('/');
        self::assertJsonResponse($response, Response::HTTP_OK);
        $data = self::decodeJson($response);
        self::assertArrayHasKey('name', $data);
        self::assertSame('Oracle TGC API', $data['name']);
        self::assertArrayHasKey('version', $data);
        self::assertArrayHasKey('endpoints', $data);
        self::assertSame('/api/auth', $data['endpoints']['auth']);
        self::assertSame('/api/users', $data['endpoints']['users']);
        self::assertSame('/api/collections', $data['endpoints']['collections']);
        self::assertSame('/api/cards', $data['endpoints']['cards']);
        self::assertSame('/api/currency', $data['endpoints']['currency']);
    }

    public function testGetApiReturnsApiInfo(): void
    {
        $response = $this->get('/api');
        self::assertJsonResponse($response, Response::HTTP_OK);
        $data = self::decodeJson($response);
        self::assertArrayHasKey('name', $data);
        self::assertArrayHasKey('docs', $data);
        self::assertSame('/docs', $data['docs']);
    }

}
