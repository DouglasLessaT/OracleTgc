<?php

namespace App\Tests\Controller\Api;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class DeckControllerTest extends ApiTestCase
{
    public function testListWithoutTokenReturns401(): void
    {
        $response = $this->get('/api/decks');
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testCreateWithoutTokenReturns401(): void
    {
        $response = $this->postJson('/api/decks', ['name' => 'Test Deck']);
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }
}
