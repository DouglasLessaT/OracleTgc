<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CardOPCG - One Piece Card Game Card
 * 
 * Representa uma carta de One Piece Card Game.
 */
#[ORM\Entity]
class CardOPCG extends Card
{
    #[ORM\Column(length: 20)]
    private string $setCode;

    #[ORM\Column(length: 255)]
    private string $setName;

    #[ORM\Column(length: 20)]
    private string $cardNumber;

    #[ORM\Column(length: 50)]
    private string $rarity; // common, uncommon, rare, etc

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $color = null; // Red, Blue, Green, Purple, Black, Yellow

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $category = null; // Leader, Character, Event, Stage

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $cost = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $power = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $counter = null;

    #[ORM\Column(type: "json")]
    private array $attributes = []; // Slash, Strike, etc

    #[ORM\Column(type: "json")]
    private array $types = []; // Straw Hat Crew, Navy, etc

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $effect = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $trigger = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $artist = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $priceUsd = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $priceJpy = null;

    public function __construct(
        string $name,
        string $setCode,
        string $setName,
        string $cardNumber,
        string $rarity
    ) {
        parent::__construct($name, 'onepiece');
        $this->setCode = $setCode;
        $this->setName = $setName;
        $this->cardNumber = $cardNumber;
        $this->rarity = $rarity;
    }

    public function getCardType(): string
    {
        return 'onepiece';
    }

    // Getters and Setters

    public function getSetCode(): string
    {
        return $this->setCode;
    }

    public function setSetCode(string $setCode): void
    {
        $this->setCode = $setCode;
        $this->markAsUpdated();
    }

    public function getSetName(): string
    {
        return $this->setName;
    }

    public function setSetName(string $setName): void
    {
        $this->setName = $setName;
        $this->markAsUpdated();
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function setCardNumber(string $cardNumber): void
    {
        $this->cardNumber = $cardNumber;
        $this->markAsUpdated();
    }

    public function getRarity(): string
    {
        return $this->rarity;
    }

    public function setRarity(string $rarity): void
    {
        $this->rarity = $rarity;
        $this->markAsUpdated();
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
        $this->markAsUpdated();
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
        $this->markAsUpdated();
    }

    public function getCost(): ?int
    {
        return $this->cost;
    }

    public function setCost(?int $cost): void
    {
        $this->cost = $cost;
        $this->markAsUpdated();
    }

    public function getPower(): ?int
    {
        return $this->power;
    }

    public function setPower(?int $power): void
    {
        $this->power = $power;
        $this->markAsUpdated();
    }

    public function getCounter(): ?int
    {
        return $this->counter;
    }

    public function setCounter(?int $counter): void
    {
        $this->counter = $counter;
        $this->markAsUpdated();
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
        $this->markAsUpdated();
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function setTypes(array $types): void
    {
        $this->types = $types;
        $this->markAsUpdated();
    }

    public function getEffect(): ?string
    {
        return $this->effect;
    }

    public function setEffect(?string $effect): void
    {
        $this->effect = $effect;
        $this->markAsUpdated();
    }

    public function getTrigger(): ?string
    {
        return $this->trigger;
    }

    public function setTrigger(?string $trigger): void
    {
        $this->trigger = $trigger;
        $this->markAsUpdated();
    }

    public function getArtist(): ?string
    {
        return $this->artist;
    }

    public function setArtist(?string $artist): void
    {
        $this->artist = $artist;
        $this->markAsUpdated();
    }

    public function getPriceUsd(): ?float
    {
        return $this->priceUsd;
    }

    public function setPriceUsd(?float $priceUsd): void
    {
        $this->priceUsd = $priceUsd;
        $this->markAsUpdated();
    }

    public function getPriceJpy(): ?float
    {
        return $this->priceJpy;
    }

    public function setPriceJpy(?float $priceJpy): void
    {
        $this->priceJpy = $priceJpy;
        $this->markAsUpdated();
    }

    /**
     * Verifica se é um Leader
     */
    public function isLeader(): bool
    {
        return $this->category === 'Leader';
    }

    /**
     * Verifica se é um Character
     */
    public function isCharacter(): bool
    {
        return $this->category === 'Character';
    }

    /**
     * Verifica se é um Event
     */
    public function isEvent(): bool
    {
        return $this->category === 'Event';
    }

    /**
     * Verifica se é um Stage
     */
    public function isStage(): bool
    {
        return $this->category === 'Stage';
    }

    /**
     * Verifica se tem trigger
     */
    public function hasTrigger(): bool
    {
        return !empty($this->trigger);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'setCode' => $this->setCode,
            'setName' => $this->setName,
            'cardNumber' => $this->cardNumber,
            'rarity' => $this->rarity,
            'color' => $this->color,
            'category' => $this->category,
            'cost' => $this->cost,
            'power' => $this->power,
            'counter' => $this->counter,
            'attributes' => $this->attributes,
            'types' => $this->types,
            'effect' => $this->effect,
            'trigger' => $this->trigger,
            'artist' => $this->artist,
            'prices' => [
                'usd' => $this->priceUsd,
                'jpy' => $this->priceJpy,
            ],
        ]);
    }
}
