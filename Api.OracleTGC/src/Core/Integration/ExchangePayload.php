<?php

namespace App\Core\Integration;

/**
 * Payload unificado para troca de dados entre sistemas heterogêneos.
 * Padroniza origem, destino, tipo e dados para APIs, filas e workers.
 */
final class ExchangePayload
{
    public function __construct(
        private string $source,
        private string $type,
        private array $data,
        private ?string $target = null,
        private array $metadata = [],
    ) {
    }

    public static function create(string $source, string $type, array $data, ?string $target = null, array $metadata = []): self
    {
        return new self($source, $type, $data, $target, $metadata);
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'type' => $this->type,
            'data' => $this->data,
            'target' => $this->target,
            'metadata' => array_merge($this->metadata, [
                'created_at' => $this->metadata['created_at'] ?? date('c'),
            ]),
        ];
    }
}
