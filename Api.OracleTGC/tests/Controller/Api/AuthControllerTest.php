<?php

namespace App\Tests\Controller\Api;

use App\Tests\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends ApiTestCase
{
    public function testRegisterWithMissingFieldsReturnsValidationError(): void
    {
        $response = $this->postJson('/api/auth/register', []);
        self::assertTrue(
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST ||
            $response->getStatusCode() === Response::HTTP_UNPROCESSABLE_ENTITY
        );
        $data = self::decodeJson($response);
        self::assertArrayHasKey('success', $data);
        self::assertFalse($data['success']);
    }

    public function testRegisterWithEmailOnlyReturnsError(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'email' => 'test@example.com',
        ]);
        self::assertTrue(
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST ||
            $response->getStatusCode() === Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    public function testLoginWithMissingCredentialsReturnsError(): void
    {
        $response = $this->postJson('/api/auth/login', []);
        self::assertTrue(
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST ||
            $response->getStatusCode() === Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    public function testLoginWithInvalidCredentialsReturnsError(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'naoexiste@test.com',
            'password' => 'wrong',
        ]);
        self::assertTrue(
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST ||
            $response->getStatusCode() === Response::HTTP_UNAUTHORIZED
        );
    }

    public function testRefreshWithoutTokenReturns401(): void
    {
        $response = $this->postJson('/api/auth/refresh', []);
        self::assertJsonResponse($response, Response::HTTP_UNAUTHORIZED);
    }

    public function testRefreshWithInvalidTokenReturnsError(): void
    {
        $response = $this->request('POST', '/api/auth/refresh', null, [
            'Authorization' => 'Bearer invalid-token',
        ]);
        self::assertTrue(
            $response->getStatusCode() === Response::HTTP_UNAUTHORIZED ||
            $response->getStatusCode() === Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Fluxo completo: registro → login → GET /api/users/me.
     * Requer banco de dados de teste disponível; é ignorado se a conexão falhar.
     */
    public function testRegisterLoginAndMeFlow(): void
    {
        $email = 'test-' . uniqid() . '@example.com';
        $password = 'SenhaSegura123!';
        $name = 'Test User';

        $reg = $this->postJson('/api/auth/register', compact('email', 'password', 'name'));
        if ($reg->getStatusCode() !== Response::HTTP_CREATED) {
            $regData = self::decodeJson($reg);
            $msg = $regData['message'] ?? $reg->getContent();
            if (str_contains((string) $msg, 'Connection refused') || str_contains((string) $msg, 'driver')) {
                self::markTestSkipped('Banco de dados de teste indisponível.');
            }
            self::assertJsonResponse($reg, Response::HTTP_CREATED);
        }
        $regData = self::decodeJson($reg);
        self::assertTrue($regData['success'] ?? false);
        self::assertArrayHasKey('data', $regData);

        $login = $this->postJson('/api/auth/login', compact('email', 'password'));
        self::assertJsonResponse($login, Response::HTTP_OK);
        $loginData = self::decodeJson($login);
        self::assertTrue($loginData['success'] ?? false);
        $token = $loginData['data']['token'] ?? $loginData['data']['access_token'] ?? null;
        self::assertNotEmpty($token);

        $me = $this->get('/api/users/me', $this->getAuthHeaders($token));
        self::assertJsonResponse($me, Response::HTTP_OK);
        $meData = self::decodeJson($me);
        self::assertTrue($meData['success'] ?? false);
        self::assertSame($email, $meData['data']['email'] ?? null);
    }
}
