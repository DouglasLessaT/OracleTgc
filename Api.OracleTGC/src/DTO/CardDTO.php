<?php

namespace App\DTO;

class CardDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $game,
        public string $set,
        public string $setName,
        public string $number,
        public string $rarity,
        public string $imageUrl,
        public ?string $artist = null,
        public bool $isFoil = false,
        public ?CardPricesDTO $prices = null,
        public ?string $type = null,
        public ?string $text = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'game' => $this->game,
            'set' => $this->set,
            'setName' => $this->setName,
            'number' => $this->number,
            'rarity' => $this->rarity,
            'imageUrl' => $this->imageUrl,
            'artist' => $this->artist,
            'isFoil' => $this->isFoil,
            'prices' => $this->prices?->toArray(),
            'type' => $this->type,
            'text' => $this->text,
        ];
    }
}

