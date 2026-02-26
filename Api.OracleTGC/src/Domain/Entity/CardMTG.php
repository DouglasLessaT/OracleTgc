<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CardMTG - Magic: The Gathering Card
 * 
 * Representa uma carta de Magic: The Gathering.
 */
#[ORM\Entity]
class CardMTG extends Card
{
    #[ORM\Column(length: 10)]
    private string $setCode;

    #[ORM\Column(length: 255)]
    private string $setName;

    #[ORM\Column(length: 20)]
    private string $collectorNumber;

    #[ORM\Column(length: 20)]
    private string $rarity; // common, uncommon, rare, mythic

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $manaCost = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeLine = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $oracleText = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $power = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $toughness = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $loyalty = null;

    #[ORM\Column(type: "json")]
    private array $colors = [];

    #[ORM\Column(type: "json")]
    private array $colorIdentity = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $artist = null;

    #[ORM\Column]
    private bool $isFoil = false;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $priceUsd = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $priceEur = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $priceTix = null;

    public function __construct(
        string $name,
        string $setCode,
        string $setName,
        string $collectorNumber,
        string $rarity
    ) {
        parent::__construct($name, 'mtg');
        $this->setCode = $setCode;
        $this->setName = $setName;
        $this->collectorNumber = $collectorNumber;
        $this->rarity = $rarity;
    }

    public function getCardType(): string
    {
        return 'mtg';
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

    public function getCollectorNumber(): string
    {
        return $this->collectorNumber;
    }

    public function setCollectorNumber(string $collectorNumber): void
    {
        $this->collectorNumber = $collectorNumber;
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

    public function getManaCost(): ?string
    {
        return $this->manaCost;
    }

    public function setManaCost(?string $manaCost): void
    {
        $this->manaCost = $manaCost;
        $this->markAsUpdated();
    }

    public function getTypeLine(): ?string
    {
        return $this->typeLine;
    }

    public function setTypeLine(?string $typeLine): void
    {
        $this->typeLine = $typeLine;
        $this->markAsUpdated();
    }

    public function getOracleText(): ?string
    {
        return $this->oracleText;
    }

    public function setOracleText(?string $oracleText): void
    {
        $this->oracleText = $oracleText;
        $this->markAsUpdated();
    }

    public function getPower(): ?string
    {
        return $this->power;
    }

    public function setPower(?string $power): void
    {
        $this->power = $power;
        $this->markAsUpdated();
    }

    public function getToughness(): ?string
    {
        return $this->toughness;
    }

    public function setToughness(?string $toughness): void
    {
        $this->toughness = $toughness;
        $this->markAsUpdated();
    }

    public function getLoyalty(): ?string
    {
        return $this->loyalty;
    }

    public function setLoyalty(?string $loyalty): void
    {
        $this->loyalty = $loyalty;
        $this->markAsUpdated();
    }

    public function getColors(): array
    {
        return $this->colors;
    }

    public function setColors(array $colors): void
    {
        $this->colors = $colors;
        $this->markAsUpdated();
    }

    public function getColorIdentity(): array
    {
        return $this->colorIdentity;
    }

    public function setColorIdentity(array $colorIdentity): void
    {
        $this->colorIdentity = $colorIdentity;
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

    public function isFoil(): bool
    {
        return $this->isFoil;
    }

    public function setIsFoil(bool $isFoil): void
    {
        $this->isFoil = $isFoil;
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

    public function getPriceEur(): ?float
    {
        return $this->priceEur;
    }

    public function setPriceEur(?float $priceEur): void
    {
        $this->priceEur = $priceEur;
        $this->markAsUpdated();
    }

    public function getPriceTix(): ?float
    {
        return $this->priceTix;
    }

    public function setPriceTix(?float $priceTix): void
    {
        $this->priceTix = $priceTix;
        $this->markAsUpdated();
    }

    /**
     * Verifica se é uma criatura
     */
    public function isCreature(): bool
    {
        return $this->typeLine && str_contains(strtolower($this->typeLine), 'creature');
    }

    /**
     * Verifica se é um planeswalker
     */
    public function isPlaneswalker(): bool
    {
        return $this->typeLine && str_contains(strtolower($this->typeLine), 'planeswalker');
    }

    /**
     * Verifica se é um terreno
     */
    public function isLand(): bool
    {
        return $this->typeLine && str_contains(strtolower($this->typeLine), 'land');
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'setCode' => $this->setCode,
            'setName' => $this->setName,
            'collectorNumber' => $this->collectorNumber,
            'rarity' => $this->rarity,
            'manaCost' => $this->manaCost,
            'typeLine' => $this->typeLine,
            'oracleText' => $this->oracleText,
            'power' => $this->power,
            'toughness' => $this->toughness,
            'loyalty' => $this->loyalty,
            'colors' => $this->colors,
            'colorIdentity' => $this->colorIdentity,
            'artist' => $this->artist,
            'isFoil' => $this->isFoil,
            'prices' => [
                'usd' => $this->priceUsd,
                'eur' => $this->priceEur,
                'tix' => $this->priceTix,
            ],
        ]);
    }
}
