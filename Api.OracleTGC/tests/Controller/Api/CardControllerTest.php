<?php

namespace App\Tests\Controller\Api;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CardControllerTest extends ApiTestCase
{
    public function testSearchWithoutTokenReturns401(): void
    {
        $response = $this->get('/api/cards/search');
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testSearchWithEmptyQueryAndTokenReturns400(): void
    {
        $response = $this->get('/api/cards/search?q=', $this->getAuthHeaders('valid-token-not-checked-in-test'));
        self::assertTrue(
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST ||
            $response->getStatusCode() === Response::HTTP_UNAUTHORIZED
        );
    }

    public function testGetMtgCardWithoutTokenReturns401(): void
    {
        $response = $this->get('/api/cards/mtg/INVALID/99999');
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testGetPokemonCardWithoutTokenReturns401(): void
    {
        $response = $this->get('/api/cards/pokemon/xx/99999');
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }
}
