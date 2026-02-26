<?php

namespace App\DTO;

class CardPricesDTO
{
    public function __construct(
        public ?float $usd = null,
        public ?float $usdFoil = null,
        public ?float $brl = null,
        public ?float $btc = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'usd' => $this->usd,
            'usdFoil' => $this->usdFoil,
            'brl' => $this->brl,
            'btc' => $this->btc,
        ];
    }
}

