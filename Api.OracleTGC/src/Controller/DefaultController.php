<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/login', name: 'login_redirect', methods: ['GET'])]
    public function loginGet(): JsonResponse
    {
        return new JsonResponse([
            'error' => 'Method not allowed',
            'message' => 'O login deve ser feito via POST em /api/auth/login com body { "email", "password" }.',
            'endpoint' => '/api/auth/login',
            'method' => 'POST',
        ], Response::HTTP_METHOD_NOT_ALLOWED, [
            'Content-Type' => 'application/json',
            'Allow' => 'POST',
        ]);
    }

    #[Route('/', name: 'api_root', methods: ['GET'])]
    #[Route('/api', name: 'api_info', methods: ['GET'])]
    public function index(): Response
    {
        return new JsonResponse([
            'name' => 'Oracle TGC API',
            'version' => '1.0',
            'docs' => '/docs',
            'endpoints' => [
                'auth' => '/api/auth',
                'users' => '/api/users',
                'collections' => '/api/collections',
                'cards' => '/api/cards',
                'currency' => '/api/currency',
            ],
        ], Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}
