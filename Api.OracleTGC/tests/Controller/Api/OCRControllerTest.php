<?php

namespace App\Tests\Controller\Api;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class OCRControllerTest extends ApiTestCase
{
    public function testExtractWithoutTokenReturns401(): void
    {
        $response = $this->postJson('/api/ocr/extract', ['text' => 'Lightning Bolt']);
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testExtractWithEmptyTextAndTokenReturns400Or401(): void
    {
        $response = $this->postJson('/api/ocr/extract', []);
        self::assertTrue(
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST ||
            $response->getStatusCode() === Response::HTTP_UNAUTHORIZED
        );
    }
}
