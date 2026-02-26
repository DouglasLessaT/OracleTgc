<?php

namespace App\Core\Integration\Auth;

use App\Domain\Entity\User;

/**
 * Bridge de autenticação.
 * Unifica o acesso ao usuário atual e validação de tokens entre sistemas (API, workers, filas).
 */
interface AuthBridgeInterface
{
    /**
     * Retorna o usuário autenticado no contexto atual (request, CLI, worker).
     * Null se não autenticado.
     */
    public function getCurrentUser(): ?User;

    /**
     * Valida um token (JWT) e retorna o usuário associado.
     * Útil para validar tokens em filas, webhooks ou outros sistemas.
     */
    public function validateToken(string $token): ?User;

    /**
     * Indica se o contexto atual está autenticado.
     */
    public function isAuthenticated(): bool;
}
