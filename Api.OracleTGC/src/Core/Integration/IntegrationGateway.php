<?php

namespace App\Core\Integration;

use App\Core\Integration\Auth\AuthBridgeInterface;
use App\Core\Integration\MessageQueue\QueueBridgeInterface;

/**
 * Gateway de integração.
 * Ponto único para autenticação unificada, filas e troca de dados entre sistemas
 * (API, workers, serviços externos, bancos de dados).
 */
final class IntegrationGateway
{
    public const SOURCE_API = 'api';
    public const SOURCE_WORKER = 'worker';
    public const SOURCE_WEBHOOK = 'webhook';
    public const SOURCE_CLI = 'cli';

    public function __construct(
        private AuthBridgeInterface $authBridge,
        private QueueBridgeInterface $queueBridge
    ) {
    }

    /**
     * Bridge de autenticação (usuário atual, validação de token).
     */
    public function auth(): AuthBridgeInterface
    {
        return $this->authBridge;
    }

    /**
     * Bridge de filas (dispatch de mensagens).
     */
    public function queue(): QueueBridgeInterface
    {
        return $this->queueBridge;
    }

    /**
     * Envia um payload para outro sistema via fila.
     * Útil para desacoplar processamento (ex: notificações, sincronização).
     */
    public function exchange(ExchangePayload $payload, string $channel = 'exchange'): ?string
    {
        return $this->queueBridge->dispatch($channel, $payload->toArray());
    }

    /**
     * Cria um payload para troca de dados.
     */
    public function createPayload(string $source, string $type, array $data, ?string $target = null, array $metadata = []): ExchangePayload
    {
        return ExchangePayload::create($source, $type, $data, $target, $metadata);
    }
}
