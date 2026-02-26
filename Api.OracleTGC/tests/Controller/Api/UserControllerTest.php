<?php

namespace App\Tests\Controller\Api;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends ApiTestCase
{
    public function testMeWithoutTokenReturns401(): void
    {
        $response = $this->get('/api/users/me');
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testMeWithInvalidTokenReturns401(): void
    {
        $response = $this->get('/api/users/me', $this->getAuthHeaders('invalid-jwt'));
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testListUsersWithoutTokenReturns401(): void
    {
        $response = $this->get('/api/users');
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }
}
