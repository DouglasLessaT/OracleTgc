<?php

namespace App\Core\Integration\MessageQueue;

/**
 * Bridge para filas de mensagens.
 * Unifica o envio de mensagens entre sistemas (API, workers, serviços externos).
 * Implementações podem usar Redis, RabbitMQ, Doctrine Messenger, etc.
 */
interface QueueBridgeInterface
{
    /**
     * Envia uma mensagem para um canal/fila.
     *
     * @param string $channel Nome do canal (ex: user_events, notifications, sync_cards)
     * @param array  $payload Dados da mensagem (serão serializados)
     * @param array  $options Opções (prioridade, delay, ttl, etc.)
     * @return string|null ID da mensagem ou null se envio síncrono/sem ID
     */
    public function dispatch(string $channel, array $payload, array $options = []): ?string;

    /**
     * Verifica se o bridge está disponível (conexão com o broker).
     */
    public function isAvailable(): bool;
}
