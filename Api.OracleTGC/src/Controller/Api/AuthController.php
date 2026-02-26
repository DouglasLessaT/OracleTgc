<?php

namespace App\Controller\Api;

use App\Core\Domain\Exception\DomainException;
use App\Core\Presentation\Controller\BaseApiController;
use App\Service\AuthService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/auth', name: 'api_auth_')]
class AuthController extends BaseApiController
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Resposta para GET em /api/auth/register (evita MethodNotAllowedHttpException).
     * Registro deve ser feito via POST com body { "email", "password", "name" }.
     */
    #[Route('/register', name: 'register_options', methods: ['GET'])]
    public function registerGet(): Response
    {
        return $this->json([
            'success' => false,
            'message' => 'Use POST com body { "email", "password", "name" } para registrar.',
            'method' => 'POST',
            'endpoint' => '/api/auth/register',
        ], Response::HTTP_METHOD_NOT_ALLOWED, [
            'Allow' => 'POST',
        ]);
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        try {
            $data = $this->getRequestData($request);
            $this->validateRequired($data, ['email', 'password', 'name']);

            $user = $this->authService->register(
                $data['email'],
                $data['password'],
                $data['name']
            );

            return $this->created($user->toArray(), 'Usuário registrado com sucesso');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao registrar usuário: ' . $e->getMessage());
        }
    }

    /**
     * Resposta amigável para GET em /api/auth/login (evita MethodNotAllowedHttpException).
     * O login deve ser feito via POST com body { "email", "password" }.
     */
    #[Route('/login', name: 'login_options', methods: ['GET'])]
    public function loginGet(): Response
    {
        return $this->json([
            'success' => false,
            'message' => 'Use POST com body { "email", "password" } para fazer login.',
            'method' => 'POST',
            'endpoint' => '/api/auth/login',
        ], Response::HTTP_METHOD_NOT_ALLOWED, [
            'Allow' => 'POST',
        ]);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): Response
    {
        try {
            $data = $this->getRequestData($request);
            $this->validateRequired($data, ['email', 'password']);

            $result = $this->authService->login(
                $data['email'],
                $data['password']
            );

            return $this->success($result, 'Login realizado com sucesso');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao fazer login: ' . $e->getMessage());
        }
    }

    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(Request $request): Response
    {
        try {
            $token = $request->headers->get('Authorization');

            if (!$token || !str_starts_with($token, 'Bearer ')) {
                return $this->unauthorized('Token não fornecido');
            }

            $token = substr($token, 7);
            $result = $this->authService->refreshToken($token);

            return $this->success($result, 'Token renovado com sucesso');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao renovar token: ' . $e->getMessage());
        }
    }

    /**
     * Verifica o código de 6 dígitos enviado por email e marca o usuário como verificado.
     * Body: { "token": "123456" }
     */
    #[Route('/verify-email', name: 'verify_email', methods: ['POST'])]
    public function verifyEmail(Request $request): Response
    {
        try {
            $data = $this->getRequestData($request);
            $this->validateRequired($data, ['token']);

            $token = $data['token'];
            if (!is_string($token) || strlen($token) !== 6 || !ctype_digit($token)) {
                return $this->validationError(['token' => 'Código deve ter 6 dígitos'], 'Dados inválidos');
            }

            $user = $this->authService->verifyEmailToken($token);
            $jwtToken = $this->authService->generateTokenForUser($user);

            return $this->success([
                'user' => $user->toArray(),
                'token' => $jwtToken,
            ], 'Email verificado com sucesso');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao verificar email: ' . $e->getMessage());
        }
    }

    /**
     * Reenvia o código de verificação para o email.
     * Body: { "email": "usuario@exemplo.com" }
     */
    #[Route('/resend-verification', name: 'resend_verification', methods: ['POST'])]
    public function resendVerification(Request $request): Response
    {
        try {
            $data = $this->getRequestData($request);
            $this->validateRequired($data, ['email']);

            $this->authService->resendVerificationCode($data['email']);

            return $this->success(null, 'Código reenviado para seu email');
        } catch (DomainException $e) {
            return $this->handleDomainException($e);
        } catch (\Exception $e) {
            return $this->error('Erro ao reenviar código: ' . $e->getMessage());
        }
    }
}

