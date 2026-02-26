<?php

namespace App\Core\Integration\Auth;

use App\Domain\Entity\User;
use App\Service\AuthService;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Implementação do bridge de autenticação.
 * Usa o Security do Symfony (request) e AuthService para validação de JWT.
 */
class AuthBridge implements AuthBridgeInterface
{
    public function __construct(
        private Security $security,
        private AuthService $authService
    ) {
    }

    public function getCurrentUser(): ?User
    {
        $user = $this->security->getUser();
        return $user instanceof User ? $user : null;
    }

    public function validateToken(string $token): ?User
    {
        return $this->authService->validateToken($token);
    }

    public function isAuthenticated(): bool
    {
        return $this->security->isGranted('IS_AUTHENTICATED_FULLY');
    }
}
