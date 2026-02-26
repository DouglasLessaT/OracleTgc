<?php

namespace App\Core\Integration\MessageQueue;

use Psr\Log\LoggerInterface;

/**
 * Implementação síncrona do bridge de filas.
 * Executa no mesmo processo (sem broker externo). Útil para desenvolvimento
 * e para troca de dados entre camadas da aplicação de forma unificada.
 * Pode ser substituído por Redis/RabbitMQ/Messenger sem mudar a interface.
 */
class SyncQueueBridge implements QueueBridgeInterface
{
    public function __construct(
        private ?LoggerInterface $logger = null
    ) {
    }

    public function dispatch(string $channel, array $payload, array $options = []): ?string
    {
        $this->logger?->debug('Queue dispatch (sync)', [
            'channel' => $channel,
            'payload_keys' => array_keys($payload),
        ]);

        // Aqui pode-se disparar event dispatcher do Symfony para handlers locais
        // ou apenas logar; quando houver broker real, enviar para a fila.
        return null;
    }

    public function isAvailable(): bool
    {
        return true;
    }
}
