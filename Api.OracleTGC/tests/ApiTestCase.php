<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base para testes de API.
 * Fornece requisições JSON e asserções de resposta.
 */
abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function request(string $method, string $uri, array $data = null, array $headers = []): Response
    {
        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];
        foreach ($headers as $key => $value) {
            $server['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            $server,
            $data !== null ? json_encode($data) : null
        );

        return $this->client->getResponse();
    }

    protected function get(string $uri, array $headers = []): Response
    {
        return $this->request(Request::METHOD_GET, $uri, null, $headers);
    }

    protected function postJson(string $uri, array $data, array $headers = []): Response
    {
        return $this->request(Request::METHOD_POST, $uri, $data, $headers);
    }

    protected function putJson(string $uri, array $data, array $headers = []): Response
    {
        return $this->request(Request::METHOD_PUT, $uri, $data, $headers);
    }

    protected function delete(string $uri, array $headers = []): Response
    {
        return $this->request(Request::METHOD_DELETE, $uri, null, $headers);
    }

    protected static function assertJsonResponse(Response $response, int $expectedStatus, string $message = ''): void
    {
        self::assertSame($expectedStatus, $response->getStatusCode(), $message ?: $response->getContent());
        self::assertStringContainsStringIgnoringCase('application/json', $response->headers->get('Content-Type', ''));
    }

    protected static function decodeJson(Response $response): array
    {
        $content = $response->getContent();
        self::assertNotFalse($content);
        $data = json_decode($content, true);
        self::assertIsArray($data, 'Response is not valid JSON: ' . substr($content, 0, 200));
        return $data;
    }

    protected function getAuthHeaders(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }
}
