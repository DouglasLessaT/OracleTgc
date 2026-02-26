<?php

namespace App\DTO;

class ExchangeRatesDTO
{
    public function __construct(
        public float $usdToBtc,
        public float $usdToBrl,
        public float $btcToUsd,
        public float $btcToBrl,
    ) {
    }

    public function toArray(): array
    {
        return [
            'usdToBtc' => $this->usdToBtc,
            'usdToBrl' => $this->usdToBrl,
            'btcToUsd' => $this->btcToUsd,
            'btcToBrl' => $this->btcToBrl,
        ];
    }
}

